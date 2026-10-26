@extends('layouts.app')

@section('title', 'Low Stock & Reorder Report')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Low Stock & Reorder Procurement List</h1>
            <p class="text-sm text-slate-500">Items that have reached or fallen below minimum buffer safety thresholds.</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('deliveries.create') }}" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                + Create Inward Delivery
            </a>
            <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition border border-slate-300">
                🖨️ Print Reorder List
            </button>
        </div>
    </div>

    <!-- Alert Summary -->
    <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-xl flex items-center justify-between shadow-xs">
        <div class="flex items-center space-x-3">
            <span class="p-2 bg-rose-100 rounded-lg text-rose-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
            <div>
                <h4 class="text-sm font-bold text-rose-900">Stock Replenishment Urgency</h4>
                <p class="text-xs text-rose-700"><strong>{{ $products->count() }}</strong> items require immediate reorder from suppliers to prevent hospital stockouts.</p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Item & SKU</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Ownership</th>
                        <th class="py-3 px-4 text-center">Current Stock</th>
                        <th class="py-3 px-4 text-center">Min Threshold</th>
                        <th class="py-3 px-4 text-center">Max Capacity</th>
                        <th class="py-3 px-4 text-center">Suggested Reorder Qty</th>
                        <th class="py-3 px-4">Preferred Supplier</th>
                        <th class="py-3 px-4 text-right">Est. Reorder Cost</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                    @php
                        $suggested = max(0, $p->max_stock_level - $p->total_stock);
                        $estCost = $suggested * $p->default_purchase_price;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <a href="{{ route('products.show', $p) }}" class="text-sky-600 hover:underline">
                                {{ $p->name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $p->sku }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600">{{ $p->category->name }}</td>
                        <td class="py-3 px-4">
                            @if($p->inventory_type === 'consignment')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">Consignment</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700">Owned</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-extrabold text-rose-600">
                            {{ $p->total_stock }} {{ $p->unit_of_measure }}
                        </td>
                        <td class="py-3 px-4 text-center font-semibold text-slate-700">{{ $p->min_stock_alert }}</td>
                        <td class="py-3 px-4 text-center text-slate-500">{{ $p->max_stock_level }}</td>
                        <td class="py-3 px-4 text-center font-extrabold text-sky-700">
                            +{{ $suggested }}
                        </td>
                        <td class="py-3 px-4 text-slate-700">
                            {{ $p->supplier ? $p->supplier->name : 'Multiple' }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900 whitespace-nowrap">
                            ₱{{ number_format($estCost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('deliveries.create') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">
                                Order &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-400">
                            All medical supplies and pharmacy items are above minimum stock levels.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
