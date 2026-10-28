@extends('layouts.app')

@section('title', 'Dispensing & Sales Ledger')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Dispensing &amp; Sales Billing</h1>
            <p class="text-xs sm:text-sm text-slate-500">Track outward stock issues, patient prescriptions, department transfers, and revenue margins.</p>
        </div>
        <a href="{{ route('sales.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm shadow-emerald-600/20 transition self-start sm:self-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ New Dispensation / Issue</span>
        </a>
    </div>

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-semibold uppercase">Gross Revenue</span>
            <div class="text-lg sm:text-2xl font-bold text-slate-900 mt-1">&#8369;{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-semibold uppercase">COGS (Stock Cost)</span>
            <div class="text-lg sm:text-2xl font-bold text-slate-700 mt-1">&#8369;{{ number_format($totalCost, 2) }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-semibold uppercase">Total Gross Profit</span>
            <div class="text-lg sm:text-2xl font-bold text-emerald-700 mt-1">&#8369;{{ number_format($totalProfit, 2) }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-semibold uppercase">Avg Margin</span>
            <div class="text-lg sm:text-2xl font-bold text-indigo-700 mt-1">{{ $avgMargin }}%</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Txn #, Patient, MRN, Dept..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <select name="transaction_type" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Transaction Types</option>
                    <option value="dispensing" {{ request('transaction_type') == 'dispensing' ? 'selected' : '' }}>Outpatient / Prescription Dispensing</option>
                    <option value="department_issue" {{ request('transaction_type') == 'department_issue' ? 'selected' : '' }}>Hospital Department Issue (ER/OR/ICU)</option>
                    <option value="walk_in_sale" {{ request('transaction_type') == 'walk_in_sale' ? 'selected' : '' }}>Walk-in Sale</option>
                </select>
            </div>

            <div>
                <select name="payment_method" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Payment Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit / Debit Card</option>
                    <option value="philhealth" {{ request('payment_method') == 'philhealth' ? 'selected' : '' }}>PhilHealth Insurance</option>
                    <option value="hmo_insurance" {{ request('payment_method') == 'hmo_insurance' ? 'selected' : '' }}>HMO / Private Insurance</option>
                    <option value="department_charge" {{ request('payment_method') == 'department_charge' ? 'selected' : '' }}>Charge to Department</option>
                    <option value="consignment_settled" {{ request('payment_method') == 'consignment_settled' ? 'selected' : '' }}>Consignment Settled</option>
                </select>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl px-4 py-2.5 transition">
                    Filter
                </button>
                <a href="{{ route('sales.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 sticky top-0">
                    <tr>
                        <th class="py-3 px-4">Txn #</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Date &amp; Time</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Patient / Ward</th>
                        <th class="py-3 px-4 hidden md:table-cell">Payment</th>
                        <th class="py-3 px-4 text-right hidden lg:table-cell">Cost</th>
                        <th class="py-3 px-4 text-right font-bold">Revenue</th>
                        <th class="py-3 px-4 text-right hidden md:table-cell">Gross Profit</th>
                        <th class="py-3 px-4 text-center hidden xl:table-cell">Margin %</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-sky-600 font-mono">
                            <a href="{{ route('sales.show', $sale) }}" class="hover:underline">
                                {{ $sale->sale_number }}
                            </a>
                            <span class="block sm:hidden text-[10px] text-slate-400 font-sans mt-0.5">{{ $sale->sale_date->format('M d, H:i') }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap hidden sm:table-cell">
                            {{ $sale->sale_date->format('M d, Y H:i') }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($sale->transaction_type === 'department_issue')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-800 border border-purple-200">Dept Issue</span>
                            @elseif($sale->transaction_type === 'dispensing')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-sky-100 text-sky-800 border border-sky-200">Rx Dispense</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-100 text-teal-800 border border-teal-200">Walk-in</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-800">
                            {{ $sale->patient_name ?: $sale->department_name ?: 'Walk-in' }}
                            @if($sale->patient_mrn)
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $sale->patient_mrn }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 capitalize text-slate-600 whitespace-nowrap hidden md:table-cell">
                            {{ str_replace('_', ' ', $sale->payment_method) }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 whitespace-nowrap hidden lg:table-cell font-mono">
                            &#8369;{{ number_format($sale->subtotal_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900 whitespace-nowrap font-mono">
                            &#8369;{{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-semibold text-emerald-700 whitespace-nowrap hidden md:table-cell font-mono">
                            &#8369;{{ number_format($sale->total_profit, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap hidden xl:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                {{ $sale->margin_percentage }}%
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 text-slate-700 rounded-lg text-xs font-semibold transition">
                                Slip &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400 text-xs">
                            No sales or dispensing transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
