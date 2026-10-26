<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Inventory Valuations
        $totalInventoryValue = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->sum(DB::raw('quantity_remaining * unit_purchase_cost'));

        $totalPharmacyValue = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereHas('product', fn($q) => $q->where('item_type', 'pharmacy'))
            ->sum(DB::raw('quantity_remaining * unit_purchase_cost'));

        $totalMedicalSupplyValue = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereHas('product', fn($q) => $q->where('item_type', 'medical_supply'))
            ->sum(DB::raw('quantity_remaining * unit_purchase_cost'));

        $totalConsignmentValue = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereHas('product', fn($q) => $q->where('inventory_type', 'consignment'))
            ->sum(DB::raw('quantity_remaining * unit_purchase_cost'));

        // 2. Deliveries & Purchasing Metrics
        $totalDeliveriesValue = Delivery::where('status', '!=', 'cancelled')->sum('total_amount');
        $deliveriesThisMonth = Delivery::where('status', '!=', 'cancelled')
            ->whereMonth('delivery_date', Carbon::now()->month)
            ->whereYear('delivery_date', Carbon::now()->year)
            ->sum('total_amount');

        $pendingPayables = Delivery::where('status', '!=', 'cancelled')
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->sum(DB::raw('total_amount - amount_paid'));

        // 3. Sales & Dispensing Financials
        $totalSalesRevenue = Sale::sum('total_amount');
        $totalCostOfGoodsSold = Sale::sum('subtotal_cost');
        $totalGrossProfit = Sale::sum('total_profit');
        $overallMargin = $totalSalesRevenue > 0 ? round(($totalGrossProfit / $totalSalesRevenue) * 100, 1) : 0;

        $salesThisMonth = Sale::whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_amount');

        $profitThisMonth = Sale::whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total_profit');

        // Hospital Extensive Consignment System (HECS) Metrics
        $hecsConsignorCollectibles = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('products.inventory_type', 'consignment')
            ->sum('sale_items.subtotal_cost');

        $hecsHospitalProfit = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('products.inventory_type', 'consignment')
            ->sum('sale_items.profit');

        $hecsGrossSales = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('products.inventory_type', 'consignment')
            ->sum('sale_items.subtotal_amount');

        // 4. Counts
        $totalProducts = Product::where('is_active', true)->count();
        $totalPharmacyProducts = Product::where('is_active', true)->where('item_type', 'pharmacy')->count();
        $totalMedicalSupplies = Product::where('is_active', true)->where('item_type', 'medical_supply')->count();
        $totalConsignmentProducts = Product::where('is_active', true)->where('inventory_type', 'consignment')->count();
        $totalSuppliers = Supplier::where('status', 'active')->count();

        // 5. Expiry & Low Stock Alerts
        $today = Carbon::today();
        $thirtyDays = Carbon::today()->addDays(30);
        $ninetyDays = Carbon::today()->addDays(90);

        $expiredBatchesCount = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '<', $today)
            ->count();

        $criticalExpiryCount = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereBetween('expiry_date', [$today, $thirtyDays])
            ->count();

        $warningExpiryCount = ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->whereBetween('expiry_date', [$thirtyDays, $ninetyDays])
            ->count();

        // Low stock items: products where total stock <= min_stock_alert
        $productsList = Product::with('batches')->where('is_active', true)->get();
        $lowStockProducts = $productsList->filter(function ($p) {
            return $p->total_stock <= $p->min_stock_alert;
        });

        // 6. Recent Activity
        $recentDeliveries = Delivery::with(['supplier', 'items'])
            ->latest()
            ->take(5)
            ->get();

        $recentDispensations = Sale::with('items.product')
            ->latest('sale_date')
            ->take(5)
            ->get();

        $expiringBatches = ProductBatch::with('product')
            ->where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->where('expiry_date', '<=', $ninetyDays)
            ->orderBy('expiry_date', 'asc')
            ->take(6)
            ->get();

        // 7. Monthly Financial Chart Data (Last 6 Months)
        $chartMonths = [];
        $deliveriesChartData = [];
        $salesChartData = [];
        $profitChartData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthLabel = $monthDate->format('M Y');
            $chartMonths[] = $monthLabel;

            $dSum = Delivery::where('status', '!=', 'cancelled')
                ->whereMonth('delivery_date', $monthDate->month)
                ->whereYear('delivery_date', $monthDate->year)
                ->sum('total_amount');
            $deliveriesChartData[] = (float) $dSum;

            $sSum = Sale::whereMonth('sale_date', $monthDate->month)
                ->whereYear('sale_date', $monthDate->year)
                ->sum('total_amount');
            $salesChartData[] = (float) $sSum;

            $pSum = Sale::whereMonth('sale_date', $monthDate->month)
                ->whereYear('sale_date', $monthDate->year)
                ->sum('total_profit');
            $profitChartData[] = (float) $pSum;
        }

        return view('dashboard', compact(
            'totalInventoryValue',
            'totalPharmacyValue',
            'totalMedicalSupplyValue',
            'totalConsignmentValue',
            'totalDeliveriesValue',
            'deliveriesThisMonth',
            'pendingPayables',
            'totalSalesRevenue',
            'totalCostOfGoodsSold',
            'totalGrossProfit',
            'overallMargin',
            'salesThisMonth',
            'profitThisMonth',
            'totalProducts',
            'totalPharmacyProducts',
            'totalMedicalSupplies',
            'totalConsignmentProducts',
            'totalSuppliers',
            'expiredBatchesCount',
            'criticalExpiryCount',
            'warningExpiryCount',
            'lowStockProducts',
            'recentDeliveries',
            'recentDispensations',
            'expiringBatches',
            'chartMonths',
            'deliveriesChartData',
            'salesChartData',
            'profitChartData',
            'hecsConsignorCollectibles',
            'hecsHospitalProfit',
            'hecsGrossSales'
        ));
    }
}
