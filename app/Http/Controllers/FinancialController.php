<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        // 1. Sales & Profit in Period
        $salesQuery = Sale::whereBetween('sale_date', [$startDate, $endDate]);
        $periodGrossRevenue = (float) $salesQuery->sum('subtotal_amount');
        $periodDiscounts = (float) $salesQuery->sum('discount_amount');
        $periodNetRevenue = (float) $salesQuery->sum('total_amount');
        $periodCOGS = (float) $salesQuery->sum('subtotal_cost');
        $periodNetProfit = (float) $salesQuery->sum('total_profit');
        $periodMargin = $periodNetRevenue > 0 ? round(($periodNetProfit / $periodNetRevenue) * 100, 1) : 0;

        // 2. Deliveries & Supplier Expenses in Period
        $deliveriesQuery = Delivery::where('status', '!=', 'cancelled')
            ->whereBetween('delivery_date', [$startDate, $endDate]);
        $periodDeliveryInvoices = (float) $deliveriesQuery->sum('total_amount');

        $periodSupplierPayments = (float) SupplierPayment::whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        // 3. Overall Accounts Payable & Asset Valuation
        $allDeliveriesTotal = (float) Delivery::where('status', '!=', 'cancelled')->sum('total_amount');
        $allPaymentsTotal = (float) SupplierPayment::sum('amount');
        $totalOutstandingPayables = max(0, $allDeliveriesTotal - $allPaymentsTotal);

        $currentInventoryValuation = (float) ProductBatch::where('status', 'active')
            ->where('quantity_remaining', '>', 0)
            ->sum(DB::raw('quantity_remaining * unit_purchase_cost'));

        // 4. Consignment Consumption & Settlement Ledger
        // When consignment items are dispensed/sold, hospital owes the supplier the batch cost!
        $consignmentSales = SaleItem::whereHas('product', fn($q) => $q->where('inventory_type', 'consignment'))
            ->whereHas('sale', fn($q) => $q->whereBetween('sale_date', [$startDate, $endDate]))
            ->with(['product.supplier', 'sale', 'batch'])
            ->latest()
            ->get();

        $consignmentTotalOwed = $consignmentSales->sum('subtotal_cost');
        $consignmentTotalRevenue = $consignmentSales->sum('subtotal_amount');
        $consignmentTotalProfit = $consignmentSales->sum('profit');

        // 5. Unpaid / Partially Paid Invoices
        $pendingInvoices = Delivery::with('supplier')
            ->where('status', '!=', 'cancelled')
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->orderBy('due_date', 'asc')
            ->get();

        // 6. Recent Payments
        $recentPayments = SupplierPayment::with(['supplier', 'delivery'])
            ->latest('payment_date')
            ->take(10)
            ->get();

        return view('financial.index', compact(
            'startDate',
            'endDate',
            'periodGrossRevenue',
            'periodDiscounts',
            'periodNetRevenue',
            'periodCOGS',
            'periodNetProfit',
            'periodMargin',
            'periodDeliveryInvoices',
            'periodSupplierPayments',
            'totalOutstandingPayables',
            'currentInventoryValuation',
            'consignmentSales',
            'consignmentTotalOwed',
            'consignmentTotalRevenue',
            'consignmentTotalProfit',
            'pendingInvoices',
            'recentPayments'
        ));
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();

        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->with('items.product')
            ->get();

        $filename = 'Financial_Report_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Txn Number', 'Date', 'Type', 'Patient/Dept', 'Gross Sales', 'COGS (Cost)', 'Discount', 'Net Total', 'Gross Profit', 'Margin %', 'Payment Method']);

            foreach ($sales as $s) {
                fputcsv($handle, [
                    $s->sale_number,
                    $s->sale_date->format('Y-m-d H:i'),
                    $s->transaction_type,
                    $s->patient_name ?: $s->department_name,
                    $s->subtotal_amount,
                    $s->subtotal_cost,
                    $s->discount_amount,
                    $s->total_amount,
                    $s->total_profit,
                    $s->margin_percentage . '%',
                    $s->payment_method,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
