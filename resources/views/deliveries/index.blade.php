@extends('layouts.app')

@section('title', 'Deliveries & Stock Receiving')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Deliveries & Stock Receiving</h1>
            <p class="text-sm text-slate-500">Track incoming shipments, batch assignments, supplier invoices, and accounts payable.</p>
        </div>
        <a href="{{ route('deliveries.create') }}" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-sky-600/20 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Record New Delivery
        </a>
    </div>

    <!-- Financial Summary Badges -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-medium uppercase">Total Deliveries Billed</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">₱{{ number_format($totalDeliveriesSum, 2) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-medium uppercase">Total Paid to Suppliers</span>
            <div class="text-2xl font-bold text-emerald-700 mt-1">₱{{ number_format($totalPaidSum, 2) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-medium uppercase">Outstanding Balance Due</span>
            <div class="text-2xl font-bold text-rose-600 mt-1">₱{{ number_format($totalPayableBalance, 2) }}</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('deliveries.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Delivery #, Invoice, Supplier..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <select name="supplier_id" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="payment_status" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Payment Statuses</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partially_paid" {{ request('payment_status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid in Full</option>
                    <option value="consignment_term" {{ request('payment_status') == 'consignment_term' ? 'selected' : '' }}>Consignment Terms</option>
                </select>
            </div>

            <div>
                <select name="status" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Delivery Statuses</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="pending_inspection" {{ request('status') == 'pending_inspection' ? 'selected' : '' }}>Pending Inspection</option>
                    <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                </select>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-lg px-3 py-2 transition">
                    Filter
                </button>
                <a href="{{ route('deliveries.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Deliveries Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Delivery Ref</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Supplier DR / Inv #</th>
                        <th class="py-3 px-4">Delivery Date</th>
                        <th class="py-3 px-4">Received By</th>
                        <th class="py-3 px-4">Items</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Amount Paid</th>
                        <th class="py-3 px-4">Balance</th>
                        <th class="py-3 px-4">Payment Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($deliveries as $del)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-sky-600">
                            <a href="{{ route('deliveries.show', $del) }}" class="hover:underline">
                                {{ $del->delivery_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-800">
                            <a href="{{ route('suppliers.show', $del->supplier) }}" class="hover:underline text-slate-700">
                                {{ $del->supplier->name }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $del->invoice_number ?: '—' }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                            {{ $del->delivery_date->format('M d, Y') }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
                            {{ $del->received_by }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-medium">
                            <span class="px-2 py-0.5 bg-slate-100 rounded-md text-slate-700">{{ $del->items->count() }} items</span>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900 whitespace-nowrap">
                            ₱{{ number_format($del->total_amount, 2) }}
                        </td>
                        <td class="py-3 px-4 text-emerald-700 font-semibold whitespace-nowrap">
                            ₱{{ number_format($del->amount_paid, 2) }}
                        </td>
                        <td class="py-3 px-4 font-semibold {{ $del->balance_due > 0 ? 'text-rose-600' : 'text-slate-400' }} whitespace-nowrap">
                            ₱{{ number_format($del->balance_due, 2) }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($del->payment_status === 'paid')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">PAID</span>
                            @elseif($del->payment_status === 'partially_paid')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">PARTIAL</span>
                            @elseif($del->payment_status === 'consignment_term')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">CONSIGNMENT</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">UNPAID</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('deliveries.show', $del) }}" class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 text-slate-700 rounded-md text-xs font-semibold transition">
                                Voucher &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-8 text-center text-slate-400">
                            No deliveries found matching your search.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
