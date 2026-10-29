@extends('layouts.app')

@section('title', 'Delivery Voucher - ' . $delivery->delivery_number)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('deliveries.index') }}" class="hover:underline flex items-center">
                    &larr; Deliveries
                </a>
                <span>/</span>
                <span class="text-slate-800 font-mono font-bold">{{ $delivery->delivery_number }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Inward Stock Receiving Voucher</h1>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center shadow-xs">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Stock Receipt</span>
            </button>
        </div>
    </div>

    <!-- Official Delivery Voucher Sheet -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8 space-y-6 printable-voucher">
        <!-- Hospital Branding & Header -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-slate-200 pb-6">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-sky-600 flex items-center justify-center text-white font-bold text-2xl shadow-sm shadow-sky-600/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900 tracking-wide">ROXAS MEMORIAL PROVINCIAL HOSPITAL</h2>
                    <p class="text-xs font-semibold text-sky-700">Central Supply Room &bull; Supply Management System</p>
                    <p class="text-[10px] text-slate-400">Arnaldo Boulevard, Roxas City, Capiz 5800</p>
                </div>
            </div>

            <div class="sm:text-right">
                <div class="flex flex-wrap items-center sm:justify-end gap-2">
                    <span class="px-2.5 py-1 bg-sky-100 text-sky-800 rounded-full text-[10px] font-bold uppercase tracking-wider">Goods Received Note (GRN)</span>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-[10px] font-bold uppercase tracking-wider">VERIFIED INWARD</span>
                </div>
                <div class="mt-2 text-xs font-mono font-black text-slate-900 text-lg">{{ $delivery->delivery_number }}</div>
                <div class="text-xs text-slate-500">Date: <strong class="text-slate-800">{{ $delivery->delivery_date->format('F d, Y') }}</strong></div>
            </div>
        </div>

        <!-- Supplier & Shipment Metadata -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-xs bg-slate-50 p-4 rounded-xl border border-slate-100">
            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Supplier / Consignor</span>
                <div class="font-bold text-slate-900 text-sm">{{ $delivery->supplier->name }}</div>
                <p class="text-slate-600 mt-0.5">Contact: {{ $delivery->supplier->contact_person ?? 'N/A' }} ({{ $delivery->supplier->phone ?? 'No phone' }})</p>
                <p class="text-slate-600">Email: {{ $delivery->supplier->email ?? 'N/A' }}</p>
            </div>

            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Delivery Reference</span>
                <div class="text-xs text-slate-700 font-mono">Invoice / DR #: <strong class="text-slate-900">{{ $delivery->invoice_number ?: 'N/A' }}</strong></div>
                <div class="text-xs text-slate-700 mt-1">Receiving Officer: <strong class="text-slate-900">{{ $delivery->received_by }}</strong></div>
                <div class="text-xs text-slate-700 mt-1">Inspection Status: <span class="capitalize font-bold text-emerald-700">{{ str_replace('_', ' ', $delivery->status) }}</span></div>
            </div>

            <div>
                <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Shipment Volume</span>
                <div class="text-xs text-slate-600">Line Items: <strong class="text-slate-900 font-bold">{{ $delivery->items->count() }} products</strong></div>
                <div class="text-xs text-slate-600 mt-1">Total Quantity Received: <strong class="text-sky-700 font-bold">{{ number_format($delivery->items->sum('quantity')) }} units</strong></div>
                @if($delivery->notes)
                    <div class="mt-2 text-[11px] text-slate-500 italic bg-white p-1.5 rounded border border-slate-200">
                        {{ $delivery->notes }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Delivered Stock Items &amp; Assigned Batches</h3>
            <div class="overflow-x-auto custom-scroll border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-700 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                        <tr>
                            <th class="py-3 px-3">#</th>
                            <th class="py-3 px-3">Item Code / Description</th>
                            <th class="py-3 px-3">Batch / Lot #</th>
                            <th class="py-3 px-3">Mfg Date</th>
                            <th class="py-3 px-3">Expiry Date</th>
                            <th class="py-3 px-3 text-right">Quantity Received</th>
                            <th class="py-3 px-3 text-right">Unit of Measure</th>
                            <th class="py-3 px-3 text-right">Unit Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-sans">
                        @foreach($delivery->items as $idx => $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-3 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3">
                                <a href="{{ route('products.show', $item->product) }}" class="font-bold text-slate-900 hover:text-sky-600">
                                    {{ $item->product->name }}
                                </a>
                                <div class="text-[10px] text-slate-500 font-mono">{{ $item->product->sku }} &bull; {{ $item->product->category?->name }}</div>
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-800">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-700">{{ $item->batch_number }}</span>
                                @if($item->lot_number)
                                    <span class="block text-[10px] text-slate-400">Lot: {{ $item->lot_number }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-500 whitespace-nowrap">
                                {{ $item->manufacture_date ? $item->manufacture_date->format('M d, Y') : '&mdash;' }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-semibold {{ $item->expiry_date->isPast() ? 'text-rose-600' : 'text-slate-800' }}">
                                    {{ $item->expiry_date->format('M d, Y') }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-black text-slate-900 font-mono text-sm">
                                {{ number_format($item->quantity) }}
                            </td>
                            <td class="py-3 px-3 text-right text-slate-600">
                                {{ $item->product->unit_of_measure }}
                            </td>
                            <td class="py-3 px-3 text-right text-slate-700 font-mono">
                                &#8369;{{ number_format($item->unit_cost, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200 font-bold">
                        <tr>
                            <td colspan="5" class="py-3 px-3 text-right uppercase text-[10px] tracking-wider text-slate-600">Total Units Received:</td>
                            <td class="py-3 px-3 text-right font-mono text-base text-sky-700">{{ number_format($delivery->items->sum('quantity')) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Official Signatures Block (Hospital Standard) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-8 border-t border-slate-200 text-center text-xs">
            <div>
                <div class="border-b border-slate-300 pb-1 mb-1 font-bold text-slate-800">{{ $delivery->supplier->name }}</div>
                <div class="text-slate-400 text-[10px]">Delivered By (Supplier Rep / Driver)</div>
            </div>
            <div>
                <div class="border-b border-slate-300 pb-1 mb-1 font-bold text-slate-800">{{ $delivery->received_by }}</div>
                <div class="text-slate-400 text-[10px]">Received & Inspected By (Supply Officer)</div>
            </div>
            <div>
                <div class="border-b border-slate-300 pb-1 mb-1 font-bold text-slate-800">LADY ORTALEZ LUCES</div>
                <div class="text-slate-400 text-[10px]">Head, Supply Section / Consignment</div>
            </div>
        </div>
    </div>

</div>
@endsection
