@extends('layouts.app')

@section('title', 'Stock Movement Audit Log')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Stock Movement & Audit Trail</h1>
            <p class="text-sm text-slate-500">Immutable chronological ledger of all inward deliveries, outward dispensations, and disposals.</p>
        </div>
        <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition border border-slate-300">
            🖨️ Print Audit Log
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('reports.audit-log') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Filter by Product Name or SKU..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <select name="movement_type" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                    <option value="">All Movement Types</option>
                    <option value="delivery_in" {{ request('movement_type') == 'delivery_in' ? 'selected' : '' }}>Inward Delivery (Stock In)</option>
                    <option value="dispense_out" {{ request('movement_type') == 'dispense_out' ? 'selected' : '' }}>Dispensed / Issued (Stock Out)</option>
                    <option value="disposal_expired" {{ request('movement_type') == 'disposal_expired' ? 'selected' : '' }}>Expired Stock Disposal</option>
                    <option value="adjustment_deduct" {{ request('movement_type') == 'adjustment_deduct' ? 'selected' : '' }}>Damaged / Lost Stock Deduct</option>
                    <option value="adjustment_add" {{ request('movement_type') == 'adjustment_add' ? 'selected' : '' }}>Physical Count Audit Add</option>
                </select>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-lg px-3 py-2 transition">
                    Filter Movements
                </button>
                <a href="{{ route('reports.audit-log') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Date / Timestamp</th>
                        <th class="py-3 px-4">Movement Action</th>
                        <th class="py-3 px-4">Medication / Supply</th>
                        <th class="py-3 px-4">Batch / Lot #</th>
                        <th class="py-3 px-4 text-right">Qty Moved</th>
                        <th class="py-3 px-4 text-right">Balance Before</th>
                        <th class="py-3 px-4 text-right">Balance After</th>
                        <th class="py-3 px-4">Reason / Reference</th>
                        <th class="py-3 px-4">Logged By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                            {{ $m->created_at->format('M d, Y • H:i') }}
                        </td>
                        <td class="py-3 px-4 font-bold whitespace-nowrap">
                            @if($m->movement_type === 'delivery_in')
                                <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-100 text-emerald-800">↓ Delivery In</span>
                            @elseif($m->movement_type === 'dispense_out')
                                <span class="px-2 py-0.5 rounded text-[10px] bg-sky-100 text-sky-800">↑ Dispensed Out</span>
                            @elseif($m->movement_type === 'disposal_expired')
                                <span class="px-2 py-0.5 rounded text-[10px] bg-rose-100 text-rose-800">✕ Expired Disposal</span>
                            @elseif($m->movement_type === 'adjustment_add')
                                <span class="px-2 py-0.5 rounded text-[10px] bg-teal-100 text-teal-800">+ Audit Add</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] bg-amber-100 text-amber-800">- Audit Deduct</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-900">
                            <a href="{{ route('products.show', $m->product) }}" class="hover:underline text-sky-700">
                                {{ $m->product->name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $m->product->sku }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-700 font-semibold">
                            {{ $m->batch ? $m->batch->batch_number : '—' }}
                        </td>
                        <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap {{ $m->quantity > 0 ? 'text-emerald-700' : 'text-slate-900' }}">
                            {{ $m->quantity > 0 ? '+' . $m->quantity : $m->quantity }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-500">{{ $m->balance_before }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">{{ $m->balance_after }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $m->reason ?: 'Standard stock transaction' }}</td>
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">{{ $m->user_name ?: 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">No stock movements recorded.</td>
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
