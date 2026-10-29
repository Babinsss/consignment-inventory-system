<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DepartmentRequest;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Requisitions & Requester Tracking Metrics (matching RMPH-supply-management)
        $pendingCount = DepartmentRequest::where('status', 'Pending')
            ->distinct('batch_id')
            ->count('batch_id');

        $totalRequisitionsCount = DepartmentRequest::distinct('batch_id')->count('batch_id');

        // 7-Day Requisition Activity Trend
        $chartDates = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartDates[] = Carbon::now()->subDays($i)->format('M d');
            $chartData[] = DepartmentRequest::whereDate('created_at', $date)
                ->distinct('batch_id')
                ->count('batch_id');
        }

        // 2. Request Processing Timeline Batches (Who requested, what department, purpose, status)
        $allRequests = DepartmentRequest::with(['product.category', 'issuer'])
            ->orderByRaw("CASE WHEN status = 'Pending' THEN 1 WHEN status = 'Approved' THEN 2 ELSE 3 END ASC")
            ->orderBy('created_at', 'desc')
            ->get();

        $groupedBatches = [];
        foreach ($allRequests as $req) {
            if (!isset($groupedBatches[$req->batch_id])) {
                $groupedBatches[$req->batch_id] = [
                    'batch_id' => $req->batch_id,
                    'created_at' => $req->created_at,
                    'department_name' => $req->department_name,
                    'requested_by' => $req->requested_by,
                    'purpose' => $req->purpose,
                    'is_emergency' => $req->is_emergency,
                    'status' => $req->status,
                    'issued_by' => $req->issuer?->name,
                    'issued_at' => $req->issued_at,
                    'items' => collect(),
                ];
            }
            $groupedBatches[$req->batch_id]['items']->push($req);
        }

        foreach ($groupedBatches as $key => $batch) {
            $hasPending = $batch['items']->contains('status', 'Pending');
            $groupedBatches[$key]['status'] = $hasPending ? 'Pending' : ($batch['items']->every(fn($i) => $i->status === 'Approved') ? 'Approved' : 'Processed');
        }

        $requestsTimeline = array_values($groupedBatches);

        // 3. Stock Monitoring & Inventory Alerts
        $productsList = Product::with(['batches', 'category'])->where('is_active', true)->get();

        // Critical / Low Stock Alert Items (total stock <= min_stock_alert)
        $criticalSupplies = $productsList->filter(function ($p) {
            return $p->total_stock <= $p->min_stock_alert;
        })->sortBy('total_stock')->values();

        $lowStockCount = $criticalSupplies->count();
        $totalProducts = $productsList->count();
        $totalPharmacyProducts = $productsList->where('item_type', 'pharmacy')->count();
        $totalMedicalSupplies = $productsList->where('item_type', 'medical_supply')->count();
        $totalConsignmentProducts = $productsList->where('inventory_type', 'consignment')->count();

        // 4. Expiry Tracking
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

        // 5. Deliveries Intake Tracking (Inward Deliveries)
        $totalDeliveriesCount = Delivery::where('status', '!=', 'cancelled')->count();
        $deliveriesThisMonthCount = Delivery::where('status', '!=', 'cancelled')
            ->whereMonth('delivery_date', Carbon::now()->month)
            ->whereYear('delivery_date', Carbon::now()->year)
            ->count();

        $recentDeliveries = Delivery::with(['supplier', 'items.product'])
            ->latest('delivery_date')
            ->take(6)
            ->get();

        $totalSuppliers = Supplier::where('status', 'active')->count();

        return view('dashboard', compact(
            'pendingCount',
            'totalRequisitionsCount',
            'chartDates',
            'chartData',
            'requestsTimeline',
            'criticalSupplies',
            'lowStockCount',
            'totalProducts',
            'totalPharmacyProducts',
            'totalMedicalSupplies',
            'totalConsignmentProducts',
            'expiredBatchesCount',
            'criticalExpiryCount',
            'warningExpiryCount',
            'totalDeliveriesCount',
            'deliveriesThisMonthCount',
            'recentDeliveries',
            'totalSuppliers'
        ));
    }
}
