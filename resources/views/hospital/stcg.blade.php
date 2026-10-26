@extends('layouts.app')

@section('title', 'Statement of Consumed Goods (StCG) - HECS')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Top Action & Filter Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Statement of Consumed Goods (StCG)</h1>
            <p class="text-sm text-slate-500">Official Hospital Extensive Consignment System (HECS) Statement for Consignor Settlement.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                🖨️ Print Official StCG Report
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs no-print">
        <form method="GET" action="{{ route('hospital.stcg') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Consignor / Supplier *</label>
                <select name="consignor_id" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 focus:ring-2 focus:ring-sky-500">
                    @foreach($consignors as $c)
                        <option value="{{ $c->id }}" {{ $selectedConsignor?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Month</label>
                <select name="month" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 focus:ring-2 focus:ring-sky-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Year</label>
                    <input type="number" name="year" value="{{ $year }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-1.5 focus:ring-2 focus:ring-sky-500">
                </div>
                <button type="submit" class="px-4 py-1.5 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-lg">
                    Generate
                </button>
            </div>
        </form>
    </div>

    <!-- Official Document Card -->
    <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-xs space-y-6">

        <!-- Official Header Matching Excel Template -->
        <div class="text-center space-y-1 border-b border-slate-200 pb-5">
            <p class="text-xs uppercase tracking-widest text-slate-500 font-semibold">Republic of the Philippines</p>
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">CAPIZ PROVINCIAL GOVERNMENT</h2>
            <h3 class="text-base font-extrabold text-sky-800 uppercase tracking-wide">HOSPITAL EXTENSIVE CONSIGNMENT SYSTEM (HECS)</h3>
            <h1 class="text-lg font-black text-slate-900 uppercase">ROXAS MEMORIAL PROVINCIAL HOSPITAL</h1>
            <div class="pt-3">
                <span class="inline-block px-4 py-1 bg-slate-100 text-slate-800 rounded font-bold text-sm uppercase tracking-wider border border-slate-300">
                    STATEMENT OF CONSUMED GOODS
                </span>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="flex justify-between text-xs text-slate-700 border-b border-slate-100 pb-3">
            <div>
                <p><strong>NAME OF CONSIGNING HOSPITAL:</strong> ROXAS MEMORIAL PROVINCIAL HOSPITAL</p>
                <p><strong>NAME OF CONSIGNOR:</strong> <span class="font-bold text-sky-900">{{ $selectedConsignor?->name ?? 'ALL CONSIGNORS' }}</span></p>
            </div>
            <div class="text-right">
                <p><strong>StCG No.:</strong> ___________________________</p>
                <p><strong>Period Covered:</strong> <span class="font-bold">{{ $periodLabel }}</span></p>
            </div>
        </div>

        <!-- StCG Table -->
        <div class="overflow-x-auto border border-slate-300 rounded-lg">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300 text-center">
                    <tr>
                        <th rowspan="2" class="py-2.5 px-3 border-r border-slate-300 text-left">Item Code</th>
                        <th rowspan="2" class="py-2.5 px-3 border-r border-slate-300 text-left min-w-[220px]">Item Description</th>
                        <th rowspan="2" class="py-2.5 px-3 border-r border-slate-300 text-right">Consignor's Price</th>
                        <th rowspan="2" class="py-2.5 px-2 border-r border-slate-300">Unit</th>
                        <th colspan="2" class="py-1 px-2 border-r border-slate-300 bg-slate-200/60">Cash</th>
                        <th colspan="2" class="py-1 px-2 border-r border-slate-300 bg-sky-50">Charge</th>
                        <th colspan="2" class="py-1 px-2 bg-emerald-50">TOTAL</th>
                    </tr>
                    <tr class="border-t border-slate-300 text-[11px]">
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Qty</th>
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Amount (₱)</th>
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Qty</th>
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Amount (₱)</th>
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Qty</th>
                        <th class="py-1 px-2 text-right">Amount (₱)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($grouped as $g)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-900 border-r border-slate-200">{{ $g['item_code'] }}</td>
                        <td class="py-2.5 px-3 font-medium text-slate-800 border-r border-slate-200">{{ $g['name'] }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-700 border-r border-slate-200">₱{{ number_format($g['consignor_price'], 2) }}</td>
                        <td class="py-2.5 px-2 text-center uppercase text-[10px] text-slate-500 border-r border-slate-200">{{ $g['unit'] }}</td>

                        <!-- Cash -->
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 text-slate-700">{{ $g['cash_qty'] ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 text-slate-700">₱{{ number_format($g['cash_amount'], 2) }}</td>

                        <!-- Charge -->
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 text-slate-700 font-semibold">{{ $g['charge_qty'] ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 text-slate-700 font-semibold">₱{{ number_format($g['charge_amount'], 2) }}</td>

                        <!-- Total -->
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 font-bold text-slate-900">{{ $g['total_qty'] }}</td>
                        <td class="py-2.5 px-2 text-right font-bold text-sky-800 whitespace-nowrap">₱{{ number_format($g['total_consignor_amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-400 italic">No consumed goods found for {{ $selectedConsignor?->name }} in {{ $periodLabel }}.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300">
                    <tr>
                        <td colspan="4" class="py-3 px-3 uppercase text-right text-[11px]">GRAND TOTAL COLLECTIBLES (Owed to Consignor):</td>
                        <td colspan="2" class="py-3 px-2 text-right border-r border-slate-300">₱{{ number_format($grouped->sum('cash_amount'), 2) }}</td>
                        <td colspan="2" class="py-3 px-2 text-right border-r border-slate-300">₱{{ number_format($grouped->sum('charge_amount'), 2) }}</td>
                        <td class="py-3 px-2 text-right border-r border-slate-300 font-extrabold text-sm">{{ number_format($grouped->sum('total_qty')) }}</td>
                        <td class="py-3 px-2 text-right font-black text-sm text-sky-800 whitespace-nowrap">₱{{ number_format($grandConsignorTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Profit Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
            <div>
                <span class="text-slate-500 uppercase font-medium block">Total Consignor Cost (Payable):</span>
                <strong class="text-base text-slate-900">₱{{ number_format($grandConsignorTotal, 2) }}</strong>
            </div>
            <div>
                <span class="text-slate-500 uppercase font-medium block">Total Hospital Gross Billed:</span>
                <strong class="text-base text-slate-900">₱{{ number_format($grandHospitalSales, 2) }}</strong>
            </div>
            <div>
                <span class="text-slate-500 uppercase font-medium block">Hospital Retained Markup (Profit):</span>
                <strong class="text-base text-emerald-700 font-extrabold">₱{{ number_format($grandHospitalProfit, 2) }}</strong>
            </div>
        </div>

        <!-- Official Signatures matching Capiz Provincial Government Format -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-8 text-xs text-center">
            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Prepared by:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">ALIANA MARIE A. DULA</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Administrative Aide III / Consignment Staff</div>
            </div>

            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Certified Correct:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">JOEMAR A. DADIVAS, RN, CNN, MAN</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Nurse 1 / Dialysis Unit Nurse Supervisor</div>
            </div>

            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Approved by:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">FLORENCIO O. LUCHING, JR., MD, FPCS</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Chief of Hospital II / Acting Chief of Hospital</div>
            </div>
        </div>

    </div>

</div>
@endsection
