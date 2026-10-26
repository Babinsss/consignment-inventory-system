<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['supplier', 'items.product'])->latest('delivery_date');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $deliveries = $query->paginate(15)->withQueryString();

        // Financial stats for deliveries
        $totalDeliveriesSum = Delivery::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalPaidSum = Delivery::where('status', '!=', 'cancelled')->sum('amount_paid');
        $totalPayableBalance = max(0, $totalDeliveriesSum - $totalPaidSum);
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('deliveries.index', compact(
            'deliveries',
            'suppliers',
            'totalDeliveriesSum',
            'totalPaidSum',
            'totalPayableBalance'
        ));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();
        $categories = Category::all();

        // Generate next delivery number
        $year = Carbon::now()->year;
        $count = Delivery::whereYear('created_at', $year)->count() + 1;
        $nextDeliveryNumber = sprintf('DEL-%s-%04d', $year, $count);

        return view('deliveries.create', compact('suppliers', 'products', 'categories', 'nextDeliveryNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_number' => 'required|unique:deliveries,delivery_number',
            'invoice_number' => 'nullable|string|max:100',
            'delivery_date' => 'required|date',
            'received_by' => 'required|string|max:255',
            'payment_status' => 'required|in:unpaid,partially_paid,paid,consignment_term',
            'amount_paid' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_number' => 'required|string|max:100',
            'items.*.lot_number' => 'nullable|string|max:100',
            'items.*.manufacture_date' => 'nullable|date',
            'items.*.expiry_date' => 'required|date|after:delivery_date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.selling_price' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += ($item['quantity'] * $item['unit_cost']);
            }

            $amountPaid = (float) ($request->amount_paid ?? 0);
            if ($request->payment_status === 'paid') {
                $amountPaid = $totalAmount;
            } elseif ($request->payment_status === 'unpaid' || $request->payment_status === 'consignment_term') {
                $amountPaid = 0;
            }

            $delivery = Delivery::create([
                'delivery_number' => $request->delivery_number,
                'invoice_number' => $request->invoice_number,
                'supplier_id' => $request->supplier_id,
                'delivery_date' => $request->delivery_date,
                'received_by' => $request->received_by,
                'status' => 'received',
                'total_amount' => $totalAmount,
                'payment_status' => $request->payment_status,
                'amount_paid' => $amountPaid,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $prod = Product::findOrFail($item['product_id']);
                $subtotal = $item['quantity'] * $item['unit_cost'];
                $sellPrice = !empty($item['selling_price']) ? $item['selling_price'] : $prod->selling_price;

                // 1. Create Batch
                $batch = ProductBatch::create([
                    'product_id' => $prod->id,
                    'delivery_id' => $delivery->id,
                    'batch_number' => $item['batch_number'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'manufacture_date' => $item['manufacture_date'] ?? null,
                    'expiry_date' => $item['expiry_date'],
                    'quantity_received' => $item['quantity'],
                    'quantity_remaining' => $item['quantity'],
                    'unit_purchase_cost' => $item['unit_cost'],
                    'unit_selling_price' => $sellPrice,
                    'status' => 'active',
                ]);

                // 2. Create Delivery Item
                DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'product_id' => $prod->id,
                    'batch_id' => $batch->id,
                    'batch_number' => $item['batch_number'],
                    'expiry_date' => $item['expiry_date'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'subtotal' => $subtotal,
                    'selling_price' => $sellPrice,
                ]);

                // 3. Record Inward Stock Movement
                $currentStock = $prod->total_stock; // before this delivery's batch was added, but batch was just created
                // To accurately reflect before/after:
                $before = $currentStock - $item['quantity'];
                $after = $currentStock;

                StockMovement::create([
                    'product_id' => $prod->id,
                    'batch_id' => $batch->id,
                    'movement_type' => 'delivery_in',
                    'quantity' => $item['quantity'],
                    'balance_before' => max(0, $before),
                    'balance_after' => $after,
                    'reference_type' => Delivery::class,
                    'reference_id' => $delivery->id,
                    'unit_cost' => $item['unit_cost'],
                    'reason' => "Delivery received: {$delivery->delivery_number}",
                    'user_name' => $delivery->received_by,
                ]);

                // Update product default cost if not set or updated
                $prod->update([
                    'default_purchase_price' => $item['unit_cost'],
                    'selling_price' => $sellPrice,
                ]);
            }

            // If an initial payment was recorded at delivery time
            if ($amountPaid > 0) {
                $payCount = SupplierPayment::count() + 1;
                $payNumber = sprintf('PAY-%s-%04d', Carbon::now()->year, $payCount);

                SupplierPayment::create([
                    'payment_number' => $payNumber,
                    'supplier_id' => $delivery->supplier_id,
                    'delivery_id' => $delivery->id,
                    'amount' => $amountPaid,
                    'payment_date' => $delivery->delivery_date,
                    'payment_method' => 'bank_transfer',
                    'reference_number' => 'Initial payment upon delivery receipt',
                    'notes' => 'Recorded during delivery intake.',
                    'recorded_by' => $delivery->received_by,
                ]);
            }

            return redirect()->route('deliveries.show', $delivery)
                ->with('success', "Delivery {$delivery->delivery_number} recorded successfully! Stock and batch inventory updated.");
        });
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['supplier', 'items.product.category', 'items.batch', 'payments']);
        return view('deliveries.show', compact('delivery'));
    }

    public function recordPayment(Request $request, Delivery $delivery)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . ($delivery->balance_due + 0.01),
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,electronic',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'recorded_by' => 'required|string|max:100',
        ]);

        return DB::transaction(function () use ($request, $delivery) {
            $payCount = SupplierPayment::count() + 1;
            $payNumber = sprintf('PAY-%s-%04d', Carbon::now()->year, $payCount);

            SupplierPayment::create([
                'payment_number' => $payNumber,
                'supplier_id' => $delivery->supplier_id,
                'delivery_id' => $delivery->id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'recorded_by' => $request->recorded_by,
            ]);

            $newPaid = $delivery->amount_paid + $request->amount;
            $newStatus = $newPaid >= $delivery->total_amount ? 'paid' : 'partially_paid';

            $delivery->update([
                'amount_paid' => $newPaid,
                'payment_status' => $newStatus,
            ]);

            return redirect()->route('deliveries.show', $delivery)
                ->with('success', "Payment of ₱" . number_format($request->amount, 2) . " recorded successfully!");
        });
    }
}
