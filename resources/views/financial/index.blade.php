@extends('layouts.app')

@section('title', 'Financial Management & Consignment Settlement')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-700 uppercase tracking-widest mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-600"></span>
                <span>Financial Management & Consignment Accounts</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Financial Ledger & Consignment Settlements</h1>
            <p class="text-xs sm:text-sm text-slate-500">Income statements, gross margins, delivery expenditures, accounts payable, and consignment vendor payables.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('financial.export', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]) }}" class="inline-flex items-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export CSV Report</span>
            </a>
        </div>
    </div>

    <!-- Date Range Filter Card -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('financial.index') }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Period:</span>
                
                <div class="flex items-center space-x-2">
                    <label class="text-slate-500 font-medium">From:</label>
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="rounded-xl border border-slate-300 px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 font-medium text-slate-800">
                </div>

                <div class="flex items-center space-x-2">
                    <label class="text-slate-500 font-medium">To:</label>
                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="rounded-xl border border-slate-300 px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 font-medium text-slate-800">
                </div>

                <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition shadow-sm shadow-emerald-600/20">
                    Update Report
                </button>
            </div>

            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 text-slate-600 font-medium">
                <a href="{{ route('financial.index', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg whitespace-nowrap transition text-xs">This Month</a>
                <a href="{{ route('financial.index', ['start_date' => now()->subMonth()->startOfMonth()->toDateString(), 'end_date' => now()->subMonth()->endOfMonth()->toDateString()]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg whitespace-nowrap transition text-xs">Last Month</a>
                <a href="{{ route('financial.index', ['start_date' => now()->startOfYear()->toDateString(), 'end_date' => now()->endOfYear()->toDateString()]) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg whitespace-nowrap transition text-xs">Year to Date</a>
            </div>
        </form>
    </div>

    <!-- Period Income Statement Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Revenue in Period -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Period Net Revenue</span>
            <div class="text-2xl font-black text-slate-900">&#8369;{{ number_format($periodNetRevenue, 2) }}</div>
            <div class="mt-2 text-xs text-slate-500 flex justify-between pt-2 border-t border-slate-100">
                <span>Gross: &#8369;{{ number_format($periodGrossRevenue, 2) }}</span>
                <span class="text-rose-600">Disc: -&#8369;{{ number_format($periodDiscounts, 2) }}</span>
            </div>
        </div>

        <!-- COGS & Gross Profit in Period -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Gross Profit (COGS)</span>
            <div class="text-2xl font-black text-emerald-700">&#8369;{{ number_format($periodNetProfit, 2) }}</div>
            <div class="mt-2 text-xs text-slate-500 flex justify-between pt-2 border-t border-slate-100">
                <span>COGS: &#8369;{{ number_format($periodCOGS, 2) }}</span>
                <span class="text-emerald-700 font-extrabold">Margin: {{ $periodMargin }}%</span>
            </div>
        </div>

        <!-- Deliveries Billed in Period -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Deliveries Billed (Inward)</span>
            <div class="text-2xl font-black text-teal-700">&#8369;{{ number_format($periodDeliveryInvoices, 2) }}</div>
            <div class="mt-2 text-xs text-slate-500 pt-2 border-t border-slate-100">
                Payments Disbursed: <strong class="text-slate-800">&#8369;{{ number_format($periodSupplierPayments, 2) }}</strong>
            </div>
        </div>

        <!-- Total Accounts Payable -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Total Accounts Payable</span>
            <div class="text-2xl font-black text-rose-600">&#8369;{{ number_format($totalOutstandingPayables, 2) }}</div>
            <div class="mt-2 text-xs text-slate-500 pt-2 border-t border-slate-100">
                Asset Valuation: <strong class="text-indigo-700">&#8369;{{ number_format($currentInventoryValuation, 2) }}</strong>
            </div>
        </div>
    </div>

    <!-- Consignment Consumption & Settlement Ledger (Key Project Requirement!) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-100 text-purple-800">
                        Consignment Stock Ledger
                    </span>
                    <h3 class="text-base font-bold text-slate-900">Consignment Stock Consumption & Payables</h3>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Items supplied on consignment consumed/dispensed in this period. The hospital owes the supplier the agreed batch settlement cost.
                </p>
            </div>

            <div class="flex items-center space-x-4 bg-purple-50/70 px-4 py-2 rounded-xl border border-purple-200/60 text-xs">
                <div>
                    <span class="text-slate-500 text-[11px] block">Owed to Vendors:</span>
                    <strong class="text-purple-900 font-extrabold text-base">&#8369;{{ number_format($consignmentTotalOwed, 2) }}</strong>
                </div>
                <div class="h-6 w-px bg-purple-200"></div>
                <div>
                    <span class="text-slate-500 text-[11px] block">Hospital Markup:</span>
                    <strong class="text-emerald-700 font-extrabold text-base">&#8369;{{ number_format($consignmentTotalProfit, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="has-scroll-hint custom-scroll overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                    <tr>
                        <th class="py-2.5 px-3">Date Dispensed</th>
                        <th class="py-2.5 px-3">Consignment Vendor</th>
                        <th class="py-2.5 px-3">Item / Description</th>
                        <th class="py-2.5 px-3">Batch / Lot #</th>
                        <th class="py-2.5 px-3">Dispense Ref</th>
                        <th class="py-2.5 px-3 text-right">Units</th>
                        <th class="py-2.5 px-3 text-right">Settlement Rate</th>
                        <th class="py-2.5 px-3 text-right">Amount Owed</th>
                        <th class="py-2.5 px-3 text-right">Billed to Patient</th>
                        <th class="py-2.5 px-3 text-right">Hospital Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($consignmentSales as $cs)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-3 text-slate-600 whitespace-nowrap">
                            {{ $cs->sale->sale_date->format('M d, Y') }}
                        </td>
                        <td class="py-3 px-3 font-semibold text-slate-800">
                            {{ $cs->product->supplier ? $cs->product->supplier->name : 'Consignment Partner' }}
                        </td>
                        <td class="py-3 px-3 font-medium text-slate-900">
                            {{ $cs->product->name }}
                        </td>
                        <td class="py-3 px-3 font-mono text-slate-700">
                            {{ $cs->batch ? $cs->batch->batch_number : '—' }}
                        </td>
                        <td class="py-3 px-3">
                            <a href="{{ route('sales.show', $cs->sale) }}" class="text-sky-600 hover:underline font-mono font-bold">
                                {{ $cs->sale->sale_number }}
                            </a>
                        </td>
                        <td class="py-3 px-3 text-right font-extrabold text-slate-900">
                            {{ $cs->quantity }}
                        </td>
                        <td class="py-3 px-3 text-right text-slate-700 whitespace-nowrap">
                            &#8369;{{ number_format($cs->unit_cost, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-purple-900 whitespace-nowrap">
                            &#8369;{{ number_format($cs->subtotal_cost, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right text-slate-900 whitespace-nowrap font-medium">
                            &#8369;{{ number_format($cs->subtotal_amount, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-emerald-700 whitespace-nowrap">
                            &#8369;{{ number_format($cs->profit, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-6 text-center text-slate-400 italic">
                            No consignment stocks consumed in this selected period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2 Column Section: Accounts Payable Aging & Recent Payments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Unpaid Deliveries / Accounts Payable List -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Unpaid Deliveries (Accounts Payable)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Invoices awaiting settlement</p>
                </div>
                <span class="text-xs font-black text-rose-600">
                    Total: &#8369;{{ number_format($pendingInvoices->sum('balance_due'), 2) }}
                </span>
            </div>

            <div class="has-scroll-hint custom-scroll overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 font-bold">
                        <tr>
                            <th class="py-2.5 px-4">Delivery #</th>
                            <th class="py-2.5 px-4">Supplier</th>
                            <th class="py-2.5 px-4">Due Date</th>
                            <th class="py-2.5 px-4 text-right">Balance Due</th>
                            <th class="py-2.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendingInvoices as $inv)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-sky-600">
                                <a href="{{ route('deliveries.show', $inv) }}" class="hover:underline">{{ $inv->delivery_number }}</a>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ Str::limit($inv->supplier->name, 22) }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                @if($inv->due_date)
                                    <span class="{{ $inv->due_date->isPast() ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                        {{ $inv->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Net 30</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-rose-600 whitespace-nowrap">
                                &#8369;{{ number_format($inv->balance_due, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('deliveries.show', $inv) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-800">
                                    Pay &rarr;
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 italic">All supplier delivery invoices have been settled in full!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Supplier Payments Disbursement Log -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Supplier Disbursement Payments Log</span>
                    </h3>
                    <p class="text-xs text-slate-500">Recent cash outflows to suppliers</p>
                </div>
            </div>

            <div class="has-scroll-hint custom-scroll overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 font-bold">
                        <tr>
                            <th class="py-2.5 px-4">Payment #</th>
                            <th class="py-2.5 px-4">Supplier</th>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4">Method</th>
                            <th class="py-2.5 px-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentPayments as $pay)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $pay->payment_number }}
                                @if($pay->delivery)
                                    <span class="block text-[10px] text-slate-400 font-normal">For: {{ $pay->delivery->delivery_number }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ Str::limit($pay->supplier->name, 20) }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $pay->payment_date->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 capitalize text-slate-600">
                                {{ str_replace('_', ' ', $pay->payment_method) }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 whitespace-nowrap">
                                &#8369;{{ number_format($pay->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 italic">No payment disbursements recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
