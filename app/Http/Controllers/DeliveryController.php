<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    /**
     * Track Inward Shipments & Deliveries
     */
    public function index(Request $request)
    {
        $query = Delivery::with(['supplier', 'items.product'])->latest('delivery_date');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('delivery_number', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('received_by', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $deliveries = $query->paginate(15)->withQueryString();

        // Operational stats for deliveries (Supply monitoring without finance)
        $totalDeliveriesCount = Delivery::where('status', '!=', 'cancelled')->count();
        $deliveriesThisMonthCount = Delivery::where('status', '!=', 'cancelled')
            ->whereMonth('delivery_date', Carbon::now()->month)
            ->whereYear('delivery_date', Carbon::now()->year)
            ->count();
        $totalItemsDelivered = DeliveryItem::sum('quantity');
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('deliveries.index', compact(
            'deliveries',
            'suppliers',
            'totalDeliveriesCount',
            'deliveriesThisMonthCount',
            'totalItemsDelivered'
        ));
    }

    /**
     * Record a New Inward Delivery
     */
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

    /**
     * Store Inward Delivery & Add to Stock Inventory
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'delivery_number' => 'required|unique:deliveries,delivery_number',
            'invoice_number' => 'nullable|string|max:100',
            'delivery_date' => 'required|date',
            'received_by' => 'required|string|max:255',
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

        DB::beginTransaction();
        try {
            // Calculate total delivery amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['unit_cost'];
            }

            // Create Delivery Record
            $delivery = Delivery::create([
                'delivery_number' => $request->delivery_number,
                'invoice_number' => $request->invoice_number,
                'supplier_id' => $request->supplier_id,
                'delivery_date' => $request->delivery_date,
                'received_by' => $request->received_by,
                'status' => 'received',
                'total_amount' => $totalAmount,
                'payment_status' => 'paid',
                'amount_paid' => $totalAmount,
                'notes' => $request->notes,
            ]);

            // Process Each Delivery Item
            foreach ($request->items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $subtotal = $itemData['quantity'] * $itemData['unit_cost'];

                // 1. Create Delivery Item
                $deliveryItem = DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'product_id' => $product->id,
                    'batch_number' => $itemData['batch_number'],
                    'lot_number' => $itemData['lot_number'] ?? null,
                    'manufacture_date' => $itemData['manufacture_date'] ?? null,
                    'expiry_date' => $itemData['expiry_date'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'subtotal' => $subtotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                // 2. Create Product Batch (Stock FEFO)
                $batch = ProductBatch::create([
                    'product_id' => $product->id,
                    'delivery_id' => $delivery->id,
                    'batch_number' => $itemData['batch_number'],
                    'lot_number' => $itemData['lot_number'] ?? null,
                    'manufacture_date' => $itemData['manufacture_date'] ?? null,
                    'expiry_date' => $itemData['expiry_date'],
                    'quantity_received' => $itemData['quantity'],
                    'quantity_remaining' => $itemData['quantity'],
                    'unit_purchase_cost' => $itemData['unit_cost'],
                    'status' => 'active',
                ]);

                // Update default purchase price & selling price if provided
                if (isset($itemData['selling_price']) && $itemData['selling_price'] > 0) {
                    $product->update([
                        'default_purchase_price' => $itemData['unit_cost'],
                        'selling_price' => $itemData['selling_price'],
                    ]);
                } else {
                    $product->update([
                        'default_purchase_price' => $itemData['unit_cost'],
                    ]);
                }

                // 3. Log Stock Movement
                $balanceBefore = $product->total_stock - $itemData['quantity'];
                StockMovement::create([
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'movement_type' => 'delivery_in',
                    'quantity' => $itemData['quantity'],
                    'balance_before' => max(0, $balanceBefore),
                    'balance_after' => $product->total_stock,
                    'reference_type' => Delivery::class,
                    'reference_id' => $delivery->id,
                    'unit_cost' => $itemData['unit_cost'],
                    'reason' => "Delivery received: {$delivery->delivery_number}" . ($delivery->invoice_number ? " (Invoice/DR: {$delivery->invoice_number})" : ''),
                    'user_name' => $request->received_by,
                ]);
            }

            DB::commit();

            return redirect()->route('deliveries.show', $delivery)
                ->with('success', "Delivery {$delivery->delivery_number} received successfully. Stock has been incremented.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to record delivery: ' . $e->getMessage());
        }
    }

    /**
     * Display Delivery Details & Received Stock
     */
    public function show(Delivery $delivery)
    {
        $delivery->load(['supplier', 'items.product.category', 'batches']);

        return view('deliveries.show', compact('delivery'));
    }
}
