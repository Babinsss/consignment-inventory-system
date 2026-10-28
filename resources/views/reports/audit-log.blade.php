@extends('layouts.app')

@section('title', 'Stock Movement Audit Log')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-700 uppercase tracking-widest mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-slate-600"></span>
                <span>Hospital Accountability Trail</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Stock Movement & Audit Trail</h1>
            <p class="text-xs sm:text-sm text-slate-500">Immutable chronological ledger of all inward deliveries, outward dispensations, adjustments, and disposals.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <button type="button" onclick="window.print()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition border border-slate-300 space-x-1.5">
                <span>🖨️</span>
                <span>Print Audit Log</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('reports.audit-log') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Filter by Product Name or SKU..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-sky-500 font-medium">
            </div>

            <div class="sm:col-span-4">
                <select name="movement_type" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-sky-500 font-medium">
                    <option value="">All Movement Types</option>
                    <option value="delivery_in" {{ request('movement_type') == 'delivery_in' ? 'selected' : '' }}>Inward Delivery (Stock In)</option>
                    <option value="dispense_out" {{ request('movement_type') == 'dispense_out' ? 'selected' : '' }}>Dispensed / Issued (Stock Out)</option>
                    <option value="disposal_expired" {{ request('movement_type') == 'disposal_expired' ? 'selected' : '' }}>Expired Stock Disposal</option>
                    <option value="adjustment_deduct" {{ request('movement_type') == 'adjustment_deduct' ? 'selected' : '' }}>Damaged / Lost Stock Deduct</option>
                    <option value="adjustment_add" {{ request('movement_type') == 'adjustment_add' ? 'selected' : '' }}>Physical Count Audit Add</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex space-x-2">
                <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl px-4 py-2 text-xs transition shadow-xs">
                    Filter
                </button>
                <a href="{{ route('reports.audit-log') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Log Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="has-scroll-hint custom-scroll overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 font-bold">
                    <tr>
                        <th class="py-3 px-4">Date / Timestamp</th>
                        <th class="py-3 px-4">Movement Action</th>
                        <th class="py-3 px-4">Medication / Supply</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Batch / Lot #</th>
                        <th class="py-3 px-4 text-right">Qty Moved</th>
                        <th class="py-3 px-4 text-right hidden md:table-cell">Bal Before</th>
                        <th class="py-3 px-4 text-right">Bal After</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Reason / Reference</th>
                        <th class="py-3 px-4 hidden lg:table-cell">Logged By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                            {{ $m->created_at->format('M d, Y • H:i') }}
                        </td>
                        <td class="py-3 px-4 font-bold whitespace-nowrap">
                            @if($m->movement_type === 'delivery_in')
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold">↓ Delivery In</span>
                            @elseif($m->movement_type === 'dispense_out')
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-sky-100 text-sky-800 font-bold">↑ Dispensed</span>
                            @elseif($m->movement_type === 'disposal_expired')
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-800 font-bold">✕ Expired Disposal</span>
                            @elseif($m->movement_type === 'adjustment_add')
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-teal-100 text-teal-800 font-bold">+ Audit Add</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800 font-bold">- Audit Deduct</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-900">
                            <a href="{{ route('products.show', $m->product) }}" class="hover:underline text-sky-700 font-bold">
                                {{ $m->product->name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $m->product->sku }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-700 font-semibold hidden sm:table-cell">
                            {{ $m->batch ? $m->batch->batch_number : '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-black whitespace-nowrap {{ $m->quantity > 0 ? 'text-emerald-700' : 'text-slate-900' }}">
                            {{ $m->quantity > 0 ? '+' . $m->quantity : $m->quantity }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-500 hidden md:table-cell">{{ $m->balance_before }}</td>
                        <td class="py-3 px-4 text-right font-black text-slate-900">{{ $m->balance_after }}</td>
                        <td class="py-3 px-4 text-slate-600 hidden sm:table-cell">{{ $m->reason ?: 'Standard stock transaction' }}</td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap hidden lg:table-cell">{{ $m->user_name ?: 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400 italic">No stock movements recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
