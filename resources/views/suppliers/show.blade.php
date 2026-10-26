@extends('layouts.app')

@section('title', $supplier->name . ' - Supplier Profile')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('suppliers.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center">
            &larr; Back to Suppliers
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('deliveries.create') }}" class="px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                + Receive Delivery from this Supplier
            </a>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300">
                Edit Supplier
            </a>
        </div>
    </div>

    <!-- Supplier Overview Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $supplier->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ ucfirst($supplier->status) }}
                    </span>
                    @if($supplier->payment_terms === 'Consignment')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-100 text-purple-800">
                            Consignment Agreement
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">
                            Terms: {{ $supplier->payment_terms }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $supplier->name }}</h1>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span>Code: <strong class="font-mono text-slate-700">{{ $supplier->code }}</strong></span>
                    @if($supplier->contact_person)
                        <span>Contact: <strong class="text-slate-700">{{ $supplier->contact_person }}</strong></span>
                    @endif
                    @if($supplier->phone)
                        <span>Phone: <strong class="text-slate-700">{{ $supplier->phone }}</strong></span>
                    @endif
                    @if($supplier->email)
                        <span>Email: <strong class="text-slate-700">{{ $supplier->email }}</strong></span>
                    @endif
                </div>
            </div>

            <!-- Balance Due -->
            <div class="sm:text-right bg-slate-50 p-4 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 font-medium uppercase block">Outstanding Balance Due</span>
                <span class="text-3xl font-extrabold {{ $supplier->balance_due > 0 ? 'text-rose-600' : 'text-slate-700' }}">
                    ₱{{ number_format($supplier->balance_due, 2) }}
                </span>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Total Purchases: ₱{{ number_format($supplier->total_purchased, 2) }} | Paid: ₱{{ number_format($supplier->total_paid, 2) }}
                </div>
            </div>
        </div>

        @if($supplier->notes)
        <div class="text-xs bg-slate-50 p-3 rounded-lg border border-slate-100 text-slate-600">
            <strong>Notes & Agreement terms:</strong> {{ $supplier->notes }}
        </div>
        @endif
    </div>

    <!-- 2 Column Section: Delivery History & Payments History -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Deliveries from this Supplier -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Deliveries Received ({{ $supplier->deliveries->count() }})</h3>
                <a href="{{ route('deliveries.create') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">+ New Delivery</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-2.5 px-3">Delivery #</th>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3 text-right">Amount</th>
                            <th class="py-2.5 px-3 text-right">Balance</th>
                            <th class="py-2.5 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($supplier->deliveries as $del)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-3 font-mono font-bold text-sky-600">
                                <a href="{{ route('deliveries.show', $del) }}" class="hover:underline">{{ $del->delivery_number }}</a>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">{{ $del->delivery_date->format('M d, Y') }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-slate-900">₱{{ number_format($del->total_amount, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold {{ $del->balance_due > 0 ? 'text-rose-600' : 'text-slate-400' }}">₱{{ number_format($del->balance_due, 2) }}</td>
                            <td class="py-2.5 px-3">
                                @if($del->payment_status === 'paid')
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800">PAID</span>
                                @elseif($del->payment_status === 'partially_paid')
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800">PARTIAL</span>
                                @elseif($del->payment_status === 'consignment_term')
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-purple-100 text-purple-800">CONSIGNMENT</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-100 text-rose-800">UNPAID</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">No deliveries recorded from this supplier yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payments Disbursed to this Supplier -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-900">Disbursement Payments ({{ $supplier->payments->count() }})</h3>
                <span class="text-xs text-slate-500">Total Paid: <strong class="text-emerald-700">₱{{ number_format($supplier->total_paid, 2) }}</strong></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-2.5 px-3">Payment #</th>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">Method</th>
                            <th class="py-2.5 px-3">Ref / Check</th>
                            <th class="py-2.5 px-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($supplier->payments as $pay)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-800">{{ $pay->payment_number }}</td>
                            <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">{{ $pay->payment_date->format('M d, Y') }}</td>
                            <td class="py-2.5 px-3 capitalize text-slate-700">{{ str_replace('_', ' ', $pay->payment_method) }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $pay->reference_number ?: '—' }}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-emerald-700">₱{{ number_format($pay->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">No payments disbursed to this supplier yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Products Supplied by this Supplier -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Catalog of Products Supplied ({{ $supplier->products->count() }})</h3>
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Product Name & SKU</th>
                        <th class="py-2.5 px-3">Category</th>
                        <th class="py-2.5 px-3">Ownership Model</th>
                        <th class="py-2.5 px-3 text-center">Current Stock</th>
                        <th class="py-2.5 px-3 text-right">Cost Price</th>
                        <th class="py-2.5 px-3 text-right">Selling Price</th>
                        <th class="py-2.5 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($supplier->products as $prod)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 px-3 font-medium text-slate-900">
                            <a href="{{ route('products.show', $prod) }}" class="text-sky-600 hover:underline">
                                {{ $prod->name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400">{{ $prod->sku }}</span>
                        </td>
                        <td class="py-2.5 px-3 text-slate-600">{{ $prod->category->name }}</td>
                        <td class="py-2.5 px-3">
                            @if($prod->inventory_type === 'consignment')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">Consignment</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700">Owned Stock</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-center font-bold text-slate-800">{{ $prod->total_stock }} {{ $prod->unit_of_measure }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-700">₱{{ number_format($prod->default_purchase_price, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">₱{{ number_format($prod->selling_price, 2) }}</td>
                        <td class="py-2.5 px-3 text-right">
                            <a href="{{ route('products.show', $prod) }}" class="text-sky-600 hover:underline">View Stock &rarr;</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-400">No products mapped directly to this supplier yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
