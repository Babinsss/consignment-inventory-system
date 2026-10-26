@extends('layouts.app')

@section('title', 'Inventory Stock Valuation Report')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inventory Stock Valuation & Asset Report</h1>
            <p class="text-sm text-slate-500">Breakdown of hospital inventory assets by category and consignment agreement.</p>
        </div>
        <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition border border-slate-300">
            🖨️ Print Valuation Report
        </button>
    </div>

    <!-- 3 Summary Valuation Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block">Total Current Stock Asset Value</span>
            <div class="text-3xl font-extrabold text-slate-900 mt-2">₱{{ number_format($totalValuation, 2) }}</div>
            <span class="text-xs text-slate-400 block mt-1">Total Active Units: <strong class="text-slate-700">{{ number_format($totalUnits) }}</strong></span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block">Owned Stock Value</span>
            <div class="text-3xl font-extrabold text-sky-700 mt-2">₱{{ number_format($ownedValuation, 2) }}</div>
            <span class="text-xs text-slate-400 block mt-1">Directly Purchased Hospital Assets</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-medium uppercase tracking-wider block">Consignment Stock Value</span>
            <div class="text-3xl font-extrabold text-purple-700 mt-2">₱{{ number_format($consignmentValuation, 2) }}</div>
            <span class="text-xs text-slate-400 block mt-1">Payable to suppliers upon usage/sale</span>
        </div>
    </div>

    <!-- Category Valuation Breakdown Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Valuation Breakdown by Category</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Category Name</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-center">Products Count</th>
                        <th class="py-3 px-4 text-center">Units on Hand</th>
                        <th class="py-3 px-4 text-right">Asset Valuation (₱)</th>
                        <th class="py-3 px-4 text-right">% of Total Inventory</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($categoryStats as $c)
                    @php
                        $pct = $totalValuation > 0 ? round(($c['valuation'] / $totalValuation) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="py-3.5 px-4 font-bold text-slate-900">{{ $c['name'] }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $c['type'] === 'pharmacy' ? 'bg-sky-100 text-sky-800' : 'bg-teal-100 text-teal-800' }}">
                                {{ $c['type'] === 'pharmacy' ? 'Pharmacy' : 'Medical Supply' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-medium text-slate-700">{{ $c['products_count'] }}</td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ number_format($c['units']) }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-slate-900">₱{{ number_format($c['valuation'], 2) }}</td>
                        <td class="py-3.5 px-4 text-right font-medium text-slate-600">
                            <div class="flex items-center justify-end space-x-2">
                                <div class="w-16 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-sky-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="w-10 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold text-slate-900 border-t border-slate-200">
                    <tr>
                        <td colspan="3" class="py-3 px-4 uppercase text-[11px]">Grand Total Inventory Assets:</td>
                        <td class="py-3 px-4 text-center text-sm font-extrabold">{{ number_format($totalUnits) }} units</td>
                        <td class="py-3 px-4 text-right text-base text-sky-700 font-extrabold">₱{{ number_format($totalValuation, 2) }}</td>
                        <td class="py-3 px-4 text-right">100.0%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
