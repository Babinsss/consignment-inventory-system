<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['items.product'])->latest('sale_date');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhere('patient_name', 'like', "%{$search}%")
                  ->orWhere('patient_mrn', 'like', "%{$search}%")
                  ->orWhere('department_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->paginate(15)->withQueryString();

        // Financial summary stats
        $totalRevenue = Sale::sum('total_amount');
        $totalCost = Sale::sum('subtotal_cost');
        $totalProfit = Sale::sum('total_profit');
        $avgMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;

        return view('sales.index', compact('sales', 'totalRevenue', 'totalCost', 'totalProfit', 'avgMargin'));
    }

    public function create()
    {
        // Load active products with available stock
        $products = Product::where('is_active', true)
            ->with(['activeBatches', 'category'])
            ->get()
            ->filter(fn($p) => $p->total_stock > 0)
            ->values();

        // Next transaction number
        $year = Carbon::now()->year;
        $count = Sale::whereYear('created_at', $year)->count() + 1;
        $nextSaleNumber = sprintf('DISP-%s-%04d', $year, $count);

        return view('sales.create', compact('products', 'nextSaleNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_number' => 'required|unique:sales,sale_number',
            'transaction_type' => 'required|in:dispensing,department_issue,walk_in_sale',
            'patient_name' => 'nullable|string|max:255',
            'patient_mrn' => 'nullable|string|max:100',
            'department_name' => 'nullable|string|max:100',
            'doctor_name' => 'nullable|string|max:100',
            'prescription_no' => 'nullable|string|max:100',
            'sale_date' => 'required|date',
            'payment_method' => 'required|in:cash,credit_card,hmo_insurance,philhealth,department_charge,consignment_settled',
            'payment_status' => 'required|in:paid,pending,waived',
            'discount_amount' => 'nullable|numeric|min:0',
            'dispensed_by' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $totalCost = 0;
            $grossSales = 0;
            $saleItemsToCreate = [];

            // Check stock and allocate using FEFO (First Expire, First Out)
            foreach ($request->items as $item) {
                $product = Product::with(['activeBatches'])->findOrFail($item['product_id']);
                $neededQty = (int) $item['quantity'];

                if ($product->total_stock < $neededQty) {
                    throw new \Exception("Insufficient stock for '{$product->name}'. Available: {$product->total_stock}, Requested: {$neededQty}");
                }

                $remainingToAllocate = $neededQty;

                foreach ($product->activeBatches as $batch) {
                    if ($remainingToAllocate <= 0) {
                        break;
                    }

                    $takeFromBatch = min($batch->quantity_remaining, $remainingToAllocate);
                    $subCost = $takeFromBatch * $batch->unit_purchase_cost;
                    $subAmount = $takeFromBatch * $item['unit_price'];
                    $profit = $subAmount - $subCost;

                    $totalCost += $subCost;
                    $grossSales += $subAmount;

                    $saleItemsToCreate[] = [
                        'product' => $product,
                        'batch' => $batch,
                        'quantity' => $takeFromBatch,
                        'unit_cost' => $batch->unit_purchase_cost,
                        'unit_price' => $item['unit_price'],
                        'subtotal_cost' => $subCost,
                        'subtotal_amount' => $subAmount,
                        'profit' => $profit,
                    ];

                    $remainingToAllocate -= $takeFromBatch;
                }

                if ($remainingToAllocate > 0) {
                    throw new \Exception("Could not allocate all requested units for '{$product->name}' from active batches.");
                }
            }

            $discount = (float) ($request->discount_amount ?? 0);
            $netTotal = max(0, $grossSales - $discount);
            $netProfit = $netTotal - $totalCost;

            $sale = Sale::create([
                'sale_number' => $request->sale_number,
                'transaction_type' => $request->transaction_type,
                'patient_name' => $request->patient_name,
                'patient_mrn' => $request->patient_mrn,
                'department_name' => $request->department_name,
                'doctor_name' => $request->doctor_name,
                'prescription_no' => $request->prescription_no,
                'sale_date' => $request->sale_date,
                'subtotal_cost' => $totalCost,
                'subtotal_amount' => $grossSales,
                'discount_amount' => $discount,
                'tax_amount' => 0.00,
                'total_amount' => $netTotal,
                'total_profit' => $netProfit,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'dispensed_by' => $request->dispensed_by,
                'notes' => $request->notes,
            ]);

            foreach ($saleItemsToCreate as $alloc) {
                $batch = $alloc['batch'];
                $prod = $alloc['product'];
                $qty = $alloc['quantity'];

                // Create Sale Item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $prod->id,
                    'batch_id' => $batch->id,
                    'quantity' => $qty,
                    'unit_cost' => $alloc['unit_cost'],
                    'unit_price' => $alloc['unit_price'],
                    'subtotal_cost' => $alloc['subtotal_cost'],
                    'subtotal_amount' => $alloc['subtotal_amount'],
                    'profit' => $alloc['profit'],
                ]);

                // Decrement batch remaining
                $beforeStock = $prod->total_stock;
                $batch->decrement('quantity_remaining', $qty);
                if ($batch->quantity_remaining <= 0) {
                    $batch->update(['status' => 'depleted']);
                }

                // Create Outward Stock Movement
                StockMovement::create([
                    'product_id' => $prod->id,
                    'batch_id' => $batch->id,
                    'movement_type' => 'dispense_out',
                    'quantity' => -$qty,
                    'balance_before' => $beforeStock,
                    'balance_after' => $beforeStock - $qty,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'unit_cost' => $alloc['unit_cost'],
                    'reason' => "Dispensation: {$sale->sale_number} to " . ($sale->patient_name ?: $sale->department_name ?: 'Walk-in'),
                    'user_name' => $sale->dispensed_by,
                ]);
            }

            return redirect()->route('sales.show', $sale)
                ->with('success', "Dispensation {$sale->sale_number} processed successfully! Revenue: ₱" . number_format($netTotal, 2) . ", Profit: ₱" . number_format($netProfit, 2));
        });
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product.category', 'items.batch']);
        return view('sales.show', compact('sale'));
    }
}
