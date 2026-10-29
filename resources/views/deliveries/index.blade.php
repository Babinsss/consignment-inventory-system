@extends('layouts.app')

@section('title', 'Deliveries & Stock Receiving')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Inward Deliveries & Stock Intake</h1>
            <p class="text-xs sm:text-sm text-slate-500">Track and monitor new inward deliveries from suppliers, consignment consignors, and stock receipts.</p>
        </div>
        <a href="{{ route('deliveries.create') }}" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm shadow-sky-600/20 transition self-start sm:self-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Record New Delivery</span>
        </a>
    </div>

    <!-- Operational Delivery Metric Badges (No Finance / Payments) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-semibold uppercase">Total Deliveries</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalDeliveriesCount) }}</div>
            <span class="text-[10px] text-slate-400">All shipments logged</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-teal-700 font-semibold uppercase">Received This Month</span>
            <div class="text-xl sm:text-2xl font-bold text-teal-700 mt-1">{{ number_format($deliveriesThisMonthCount) }}</div>
            <span class="text-[10px] text-slate-400">{{ now()->format('F Y') }}</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-sky-700 font-semibold uppercase">Active Suppliers</span>
            <div class="text-xl sm:text-2xl font-bold text-sky-700 mt-1">{{ $suppliers->count() }}</div>
            <span class="text-[10px] text-slate-400">Vendors & Consignors</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-purple-700 font-semibold uppercase">Total Stock Intake</span>
            <div class="text-xl sm:text-2xl font-bold text-purple-700 mt-1">{{ number_format($totalItemsDelivered) }}</div>
            <span class="text-[10px] text-slate-400">Total units received</span>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('deliveries.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Delivery #, Invoice, Supplier, Receiver..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <select name="supplier_id" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Suppliers / Consignors</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Delivery Statuses</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="pending_inspection" {{ request('status') == 'pending_inspection' ? 'selected' : '' }}>Pending Inspection</option>
                    <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                </select>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl px-4 py-2.5 transition">
                    Filter
                </button>
                <a href="{{ route('deliveries.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Deliveries Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 sticky top-0">
                    <tr>
                        <th class="py-3 px-4">Delivery Ref</th>
                        <th class="py-3 px-4">Supplier / Consignor</th>
                        <th class="py-3 px-4 hidden md:table-cell">Supplier Inv / DR #</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Delivery Date</th>
                        <th class="py-3 px-4 hidden lg:table-cell">Received By</th>
                        <th class="py-3 px-4">Items / Units</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deliveries as $del)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-sky-600 font-mono">
                            <a href="{{ route('deliveries.show', $del) }}" class="hover:underline">
                                {{ $del->delivery_number }}
                            </a>
                            <span class="block sm:hidden text-[10px] text-slate-400 font-sans mt-0.5">{{ $del->delivery_date->format('M d, Y') }}</span>
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-800">
                            <a href="{{ route('suppliers.show', $del->supplier) }}" class="hover:underline text-slate-800 font-semibold block leading-tight">
                                {{ Str::limit($del->supplier->name, 28) }}
                            </a>
                            <span class="block text-[10px] text-slate-400 mt-0.5 sm:hidden">{{ $del->items->count() }} line items</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 hidden md:table-cell font-mono">
                            {{ $del->invoice_number ?: '&mdash;' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap hidden sm:table-cell">
                            {{ $del->delivery_date->format('M d, Y') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 hidden lg:table-cell">
                            <span class="font-medium text-slate-700">{{ $del->received_by }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-medium whitespace-nowrap">
                            <span class="px-2.5 py-1 bg-slate-100 rounded-lg text-slate-800 font-bold text-[11px]">
                                {{ $del->items->count() }} items ({{ $del->items->sum('quantity') }} units)
                            </span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($del->status === 'received')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">RECEIVED</span>
                            @elseif($del->status === 'pending_inspection')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">INSPECTION</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ strtoupper($del->status) }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('deliveries.show', $del) }}" class="inline-flex items-center px-3 py-1 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 text-slate-700 rounded-lg text-xs font-semibold transition">
                                View Items &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p class="font-semibold text-slate-600">No deliveries found matching your search</p>
                            <p class="text-xs text-slate-400 mt-1">Try clearing your filters or record a new delivery</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deliveries->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
