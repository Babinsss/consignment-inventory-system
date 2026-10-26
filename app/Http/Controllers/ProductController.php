<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'supplier', 'batches' => function ($q) {
            $q->where('status', 'active')->where('quantity_remaining', '>', 0);
        }]);

        // Filter: Item Type (Medical Supplies vs Pharmacy Stocks)
        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        // Filter: Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter: Inventory Type (Owned vs Consignment)
        if ($request->filled('inventory_type')) {
            $query->where('inventory_type', $request->inventory_type);
        }

        // Filter: Storage Condition
        if ($request->filled('storage_condition')) {
            $query->where('storage_condition', $request->storage_condition);
        }

        // Filter: Search Keyword
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('brand_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $allProducts = $query->latest()->get();

        // Stock status filtering in collection to utilize computed accessor
        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            $allProducts = $allProducts->filter(function ($prod) use ($status) {
                return $prod->stock_status === $status;
            });
        }

        // Expiry alert filtering
        if ($request->filled('expiry_filter')) {
            $exp = $request->expiry_filter;
            $allProducts = $allProducts->filter(function ($prod) use ($exp) {
                return $prod->expiry_alert === $exp;
            });
        }

        // Manual pagination
        $perPage = 15;
        $page = $request->input('page', 1);
        $total = $allProducts->count();
        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $allProducts->forPage($page, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categories = Category::all();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        // Summary KPI counts for top pill navigation
        $totalPharmacyCount = Product::where('item_type', 'pharmacy')->count();
        $totalMedicalCount = Product::where('item_type', 'medical_supply')->count();
        $totalConsignmentCount = Product::where('inventory_type', 'consignment')->count();
        $totalLowStockCount = Product::all()->filter(fn($p) => $p->stock_status === 'low_stock' || $p->stock_status === 'out_of_stock')->count();

        return view('products.index', compact(
            'products',
            'categories',
            'suppliers',
            'totalPharmacyCount',
            'totalMedicalCount',
            'totalConsignmentCount',
            'totalLowStockCount'
        ));
    }

    public function create()
    {
        $categories = Category::all();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'item_type' => 'required|in:medical_supply,pharmacy',
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'inventory_type' => 'required|in:owned,consignment',
            'min_stock_alert' => 'required|integer|min:0',
            'max_stock_level' => 'required|integer|min:1',
            'default_purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'storage_condition' => 'required|in:room_temperature,refrigerated_2_8c,cold_storage,controlled_substance',
            'description' => 'nullable|string',
        ]);

        $product = Product::create($validated);

        return redirect()->route('products.show', $product)
            ->with('success', "Item {$product->name} [{$product->sku}] added successfully!");
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'supplier',
            'batches' => fn($q) => $q->orderBy('expiry_date', 'asc'),
            'stockMovements' => fn($q) => $q->latest()->take(20),
        ]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'item_type' => 'required|in:medical_supply,pharmacy',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:100',
            'unit_of_measure' => 'required|string|max:50',
            'inventory_type' => 'required|in:owned,consignment',
            'min_stock_alert' => 'required|integer|min:0',
            'max_stock_level' => 'required|integer|min:1',
            'default_purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'storage_condition' => 'required|in:room_temperature,refrigerated_2_8c,cold_storage,controlled_substance',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $product->update($validated);

        return redirect()->route('products.show', $product)
            ->with('success', "Item {$product->name} updated successfully!");
    }

    public function adjustStock(Request $request, Product $product)
    {
        $request->validate([
            'batch_id' => 'required|exists:product_batches,id',
            'adjustment_type' => 'required|in:adjustment_add,adjustment_deduct,disposal_expired',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'user_name' => 'required|string|max:100',
        ]);

        $batch = ProductBatch::where('product_id', $product->id)->findOrFail($request->batch_id);

        $beforeTotal = $product->total_stock;
        $qty = $request->quantity;

        if ($request->adjustment_type === 'adjustment_add') {
            $batch->increment('quantity_remaining', $qty);
            $afterTotal = $beforeTotal + $qty;
            $movementQty = $qty;
        } else {
            if ($qty > $batch->quantity_remaining) {
                return back()->with('error', "Cannot deduct {$qty} units. Batch only has {$batch->quantity_remaining} units remaining.");
            }
            $batch->decrement('quantity_remaining', $qty);
            if ($batch->quantity_remaining === 0) {
                $batch->update(['status' => $request->adjustment_type === 'disposal_expired' ? 'expired' : 'depleted']);
            }
            $afterTotal = $beforeTotal - $qty;
            $movementQty = -$qty;
        }

        StockMovement::create([
            'product_id' => $product->id,
            'batch_id' => $batch->id,
            'movement_type' => $request->adjustment_type,
            'quantity' => $movementQty,
            'balance_before' => $beforeTotal,
            'balance_after' => $afterTotal,
            'unit_cost' => $batch->unit_purchase_cost,
            'reason' => $request->reason,
            'user_name' => $request->user_name,
        ]);

        return redirect()->route('products.show', $product)
            ->with('success', "Stock adjustment of {$qty} units recorded.");
    }
}
