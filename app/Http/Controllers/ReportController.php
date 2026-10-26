<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function expiry(Request $request)
    {
        $daysThreshold = (int) $request->input('days', 90);
        $thresholdDate = Carbon::today()->addDays($daysThreshold);

        $batches = ProductBatch::with(['product.category', 'delivery.supplier'])
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '<=', $thresholdDate)
            ->orderBy('expiry_date', 'asc')
            ->paginate(20)
            ->withQueryString();

        $expiredCount = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '<', Carbon::today())
            ->count();

        $criticalCount = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->addDays(30)])
            ->count();

        $warningCount = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereBetween('expiry_date', [Carbon::today()->addDays(31), Carbon::today()->addDays(90)])
            ->count();

        return view('reports.expiry', compact('batches', 'daysThreshold', 'expiredCount', 'criticalCount', 'warningCount'));
    }

    public function valuation(Request $request)
    {
        $categories = Category::with(['products.batches' => function ($q) {
            $q->where('status', 'active')->where('quantity_remaining', '>', 0);
        }])->get();

        $categoryStats = $categories->map(function ($cat) {
            $totalUnits = 0;
            $totalValuation = 0.0;
            $productCount = $cat->products->count();

            foreach ($cat->products as $p) {
                foreach ($p->batches as $b) {
                    $totalUnits += $b->quantity_remaining;
                    $totalValuation += ($b->quantity_remaining * $b->unit_purchase_cost);
                }
            }

            return [
                'name' => $cat->name,
                'type' => $cat->type,
                'products_count' => $productCount,
                'units' => $totalUnits,
                'valuation' => $totalValuation,
            ];
        });

        $totalValuation = $categoryStats->sum('valuation');
        $totalUnits = $categoryStats->sum('units');

        // Consignment vs Owned breakdown
        $ownedValuation = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereHas('product', fn($q) => $q->where('inventory_type', 'owned'))
            ->sum(DB::raw('quantity_remaining * unit_purchase_cost'));

        $consignmentValuation = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereHas('product', fn($q) => $q->where('inventory_type', 'consignment'))
            ->sum(DB::raw('quantity_remaining * unit_purchase_cost'));

        return view('reports.valuation', compact(
            'categoryStats',
            'totalValuation',
            'totalUnits',
            'ownedValuation',
            'consignmentValuation'
        ));
    }

    public function lowStock()
    {
        $products = Product::with(['category', 'supplier', 'batches'])
            ->where('is_active', true)
            ->get()
            ->filter(fn($p) => $p->total_stock <= $p->min_stock_alert)
            ->sortBy('total_stock');

        return view('reports.low-stock', compact('products'));
    }

    public function auditLog(Request $request)
    {
        $query = StockMovement::with(['product', 'batch'])->latest();

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $movements = $query->paginate(25)->withQueryString();

        return view('reports.audit-log', compact('movements'));
    }
}
