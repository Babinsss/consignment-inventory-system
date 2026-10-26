@extends('layouts.app')

@section('title', 'Dispensation Receipt - ' . $sale->sale_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center">
            &larr; Back to Dispensing Ledger
        </a>
        <div class="flex items-center space-x-2">
            <a href="{{ route('sales.create') }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                + New Dispense
            </a>
            <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300">
                🖨️ Print Receipt / Slip
            </button>
        </div>
    </div>

    <!-- Official Dispensation Slip Document -->
    <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-xs space-y-6">

        <!-- Slip Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-200 pb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold uppercase tracking-wider">Pharmacy Dispensation & Charge Slip</span>
                    <span class="px-2.5 py-1 bg-sky-100 text-sky-800 rounded-lg text-xs font-bold uppercase">{{ str_replace('_', ' ', $sale->transaction_type) }}</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 mt-2 tracking-tight">{{ $sale->sale_number }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Dispensed On: <strong class="text-slate-700">{{ $sale->sale_date->format('F d, Y • h:i A') }}</strong></p>
            </div>

            <div class="text-left sm:text-right text-xs text-slate-600 space-y-1">
                <div>Payment Method: <strong class="text-slate-900 uppercase">{{ str_replace('_', ' ', $sale->payment_method) }}</strong></div>
                <div>Status: <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] bg-emerald-100 text-emerald-800">{{ $sale->payment_status }}</span></div>
                <div>Dispensed By: <strong class="text-slate-900">{{ $sale->dispensed_by }}</strong></div>
            </div>
        </div>

        <!-- Recipient Information Strip -->
        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Recipient / Patient</span>
                <h3 class="text-sm font-bold text-slate-900">
                    {{ $sale->patient_name ?: $sale->department_name ?: 'Walk-in Customer' }}
                </h3>
                @if($sale->patient_mrn)
                    <p class="text-slate-600">MRN / Dept Code: <span class="font-mono font-semibold">{{ $sale->patient_mrn }}</span></p>
                @endif
                @if($sale->department_name)
                    <p class="text-slate-600">Department / Unit: <span class="font-semibold">{{ $sale->department_name }}</span></p>
                @endif
            </div>

            <div>
                <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Prescription & Clinical Details</span>
                <p class="text-slate-600">Physician: <span class="font-semibold text-slate-800">{{ $sale->doctor_name ?: 'Hospital Staff / Attending' }}</span></p>
                <p class="text-slate-600">Prescription No: <span class="font-mono text-slate-800">{{ $sale->prescription_no ?: 'Standard Order' }}</span></p>
                @if($sale->notes)
                    <p class="text-slate-600 mt-1 italic">Notes: {{ $sale->notes }}</p>
                @endif
            </div>
        </div>

        <!-- Dispensed Line Items with FEFO Batch Traceability -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Dispensed Items (FEFO Batch Deductions)</h3>
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Medication / Supply</th>
                            <th class="py-2.5 px-3">Batch / Lot Deducted</th>
                            <th class="py-2.5 px-3">Batch Expiry</th>
                            <th class="py-2.5 px-3 text-right">Qty</th>
                            <th class="py-2.5 px-3 text-right">Unit Price</th>
                            <th class="py-2.5 px-3 text-right">Batch Unit Cost</th>
                            <th class="py-2.5 px-3 text-right">Line Total</th>
                            <th class="py-2.5 px-3 text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sale->items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3">
                                <a href="{{ route('products.show', $item->product) }}" class="font-bold text-slate-900 hover:text-sky-600 hover:underline">
                                    {{ $item->product->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $item->product->sku }}</span>
                            </td>
                            <td class="py-3 px-3 font-mono font-semibold text-slate-800">
                                {{ $item->batch ? $item->batch->batch_number : 'Manual Batch' }}
                            </td>
                            <td class="py-3 px-3 text-slate-600 whitespace-nowrap">
                                {{ $item->batch ? $item->batch->expiry_date->format('M d, Y') : '—' }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900">
                                {{ $item->quantity }} <span class="text-[10px] text-slate-400 font-normal">{{ $item->product->unit_of_measure }}</span>
                            </td>
                            <td class="py-3 px-3 text-right text-slate-700">
                                ₱{{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right text-slate-500">
                                ₱{{ number_format($item->unit_cost, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900">
                                ₱{{ number_format($item->subtotal_amount, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-semibold text-emerald-700">
                                ₱{{ number_format($item->profit, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Financial Summary Totals -->
        <div class="flex justify-end">
            <div class="w-full sm:w-80 bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Gross Sales:</span>
                    <span class="font-semibold text-slate-800">₱{{ number_format($sale->subtotal_amount, 2) }}</span>
                </div>
                @if($sale->discount_amount > 0)
                <div class="flex justify-between text-rose-600 font-semibold">
                    <span>Discount:</span>
                    <span>-₱{{ number_format($sale->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>Cost of Goods Sold (COGS):</span>
                    <span class="text-slate-600">₱{{ number_format($sale->subtotal_cost, 2) }}</span>
                </div>
                <div class="flex justify-between text-emerald-700 font-bold border-t border-slate-200 pt-1">
                    <span>Gross Margin Profit:</span>
                    <span>₱{{ number_format($sale->total_profit, 2) }} ({{ $sale->margin_percentage }}%)</span>
                </div>
                <div class="flex justify-between items-center text-slate-900 border-t border-slate-300 pt-2 text-base font-extrabold">
                    <span>Total Billed:</span>
                    <span class="text-emerald-700 text-xl">₱{{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Signatures for Receipt -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-2 gap-8 text-xs text-center">
            <div>
                <div class="border-b border-slate-300 pb-1 mb-1 font-semibold text-slate-800">{{ $sale->dispensed_by }}</div>
                <div class="text-slate-400 text-[10px]">Registered Pharmacist / Dispensing Officer</div>
            </div>
            <div>
                <div class="border-b border-slate-300 pb-1 mb-1 font-semibold text-slate-800">
                    {{ $sale->patient_name ?: $sale->department_name ?: 'Recipient' }}
                </div>
                <div class="text-slate-400 text-[10px]">Patient / Department Representative Signature</div>
            </div>
        </div>

    </div>

</div>
@endsection
