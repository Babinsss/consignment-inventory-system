@extends('layouts.app')

@section('title', 'Statement of Consumed Goods (StCG) - HECS')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Top Action & Filter Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-purple-700 uppercase tracking-widest mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-purple-600"></span>
                <span>HECS Hospital Form • Capiz Provincial Government</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Statement of Consumed Goods (StCG)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Official statement for consignor settlement, monthly consumption totals, and hospital retained margin.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <button type="button" onclick="window.print()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition space-x-2">
                <span>🖨️</span>
                <span>Print Official StCG</span>
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs no-print">
        <form method="GET" action="{{ route('hospital.stcg') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Select Consignor / Supplier *</label>
                <select name="consignor_id" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500 font-medium">
                    @foreach($consignors as $c)
                        <option value="{{ $c->id }}" {{ $selectedConsignor?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Month</label>
                <select name="month" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500 font-medium">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500 font-mono font-medium">
            </div>

            <div class="sm:col-span-2 flex items-end">
                <button type="submit" class="w-full py-2 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs transition shadow-sm shadow-purple-600/20">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Official Document Card -->
    <div class="bg-white rounded-2xl p-5 sm:p-8 border border-slate-200 shadow-xs space-y-6">

        <!-- Official Header Matching Excel Template -->
        <div class="text-center space-y-1 border-b border-slate-200 pb-5">
            <p class="text-[11px] uppercase tracking-widest text-slate-500 font-semibold">Republic of the Philippines</p>
            <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">CAPIZ PROVINCIAL GOVERNMENT</h2>
            <h3 class="text-sm sm:text-base font-extrabold text-sky-800 uppercase tracking-wide">HOSPITAL EXTENSIVE CONSIGNMENT SYSTEM (HECS)</h3>
            <h1 class="text-base sm:text-lg font-black text-slate-900 uppercase">ROXAS MEMORIAL PROVINCIAL HOSPITAL</h1>
            <div class="pt-2">
                <span class="inline-block px-4 py-1 bg-slate-100 text-slate-800 rounded-lg font-bold text-xs sm:text-sm uppercase tracking-wider border border-slate-300">
                    STATEMENT OF CONSUMED GOODS
                </span>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-700 border-b border-slate-100 pb-3 gap-2">
            <div>
                <p><strong>NAME OF CONSIGNING HOSPITAL:</strong> ROXAS MEMORIAL PROVINCIAL HOSPITAL</p>
                <p><strong>NAME OF CONSIGNOR:</strong> <span class="font-bold text-sky-900">{{ $selectedConsignor?->name ?? 'ALL CONSIGNORS' }}</span></p>
            </div>
            <div class="sm:text-right">
                <p><strong>StCG No.:</strong> ___________________________</p>
                <p><strong>Period Covered:</strong> <span class="font-bold">{{ $periodLabel }}</span></p>
            </div>
        </div>

        <!-- Mobile Card View (shown only on small screens) -->
        <div class="block md:hidden space-y-3 no-print">
            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold uppercase tracking-wider px-1">
                <span>Consumed Items ({{ $grouped->count() }})</span>
                <span>Tap for details</span>
            </div>
            @forelse($grouped as $g)
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-mono text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200/60">{{ $g['item_code'] }}</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-1">{{ $g['name'] }}</h4>
                    </div>
                    <span class="text-[10px] uppercase font-bold text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200 shrink-0">{{ $g['unit'] }}</span>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[11px] pt-1 border-t border-slate-200/60">
                    <div>
                        <span class="text-slate-400 block">Consignor Price:</span>
                        <span class="font-semibold text-slate-800">&#8369;{{ number_format($g['consignor_price'], 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block">Total Consumed Qty:</span>
                        <span class="font-bold text-slate-900">{{ $g['total_qty'] }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[11px] bg-white p-2 rounded-lg border border-slate-200/80">
                    <div>
                        <span class="text-slate-500 text-[10px]">Cash: {{ $g['cash_qty'] ?: 0 }} pcs</span>
                        <span class="block font-semibold text-slate-700">&#8369;{{ number_format($g['cash_amount'], 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-500 text-[10px]">Charge: {{ $g['charge_qty'] ?: 0 }} pcs</span>
                        <span class="block font-semibold text-slate-700">&#8369;{{ number_format($g['charge_amount'], 2) }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/60">
                    <span class="text-slate-500 font-bold uppercase text-[10px]">Total Consignor Payable:</span>
                    <span class="font-black text-sky-800 text-sm">&#8369;{{ number_format($g['total_consignor_amount'], 2) }}</span>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                No consumed goods found for {{ $selectedConsignor?->name }} in {{ $periodLabel }}.
            </div>
            @endforelse
        </div>

        <!-- Desktop StCG 10-Column Table (Also used for Print) -->
        <div class="hidden md:block has-scroll-hint custom-scroll overflow-x-auto border border-slate-300 rounded-xl">
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
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Amount (&#8369;)</th>
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Qty</th>
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Amount (&#8369;)</th>
                        <th class="py-1 px-2 border-r border-slate-300 text-right">Qty</th>
                        <th class="py-1 px-2 text-right">Amount (&#8369;)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($grouped as $g)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-900 border-r border-slate-200">{{ $g['item_code'] }}</td>
                        <td class="py-2.5 px-3 font-medium text-slate-800 border-r border-slate-200">{{ $g['name'] }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-700 border-r border-slate-200">&#8369;{{ number_format($g['consignor_price'], 2) }}</td>
                        <td class="py-2.5 px-2 text-center uppercase text-[10px] text-slate-500 border-r border-slate-200">{{ $g['unit'] }}</td>

                        <!-- Cash -->
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 text-slate-700">{{ $g['cash_qty'] ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 text-slate-700">&#8369;{{ number_format($g['cash_amount'], 2) }}</td>

                        <!-- Charge -->
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 text-slate-700 font-semibold">{{ $g['charge_qty'] ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 text-slate-700 font-semibold">&#8369;{{ number_format($g['charge_amount'], 2) }}</td>

                        <!-- Total -->
                        <td class="py-2.5 px-2 text-right border-r border-slate-200 font-bold text-slate-900">{{ $g['total_qty'] }}</td>
                        <td class="py-2.5 px-2 text-right font-bold text-sky-800 whitespace-nowrap">&#8369;{{ number_format($g['total_consignor_amount'], 2) }}</td>
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
                        <td colspan="2" class="py-3 px-2 text-right border-r border-slate-300 font-semibold">&#8369;{{ number_format($grouped->sum('cash_amount'), 2) }}</td>
                        <td colspan="2" class="py-3 px-2 text-right border-r border-slate-300 font-semibold">&#8369;{{ number_format($grouped->sum('charge_amount'), 2) }}</td>
                        <td class="py-3 px-2 text-right border-r border-slate-300 font-extrabold text-sm">{{ number_format($grouped->sum('total_qty')) }}</td>
                        <td class="py-3 px-2 text-right font-black text-sm text-sky-800 whitespace-nowrap">&#8369;{{ number_format($grandConsignorTotal, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Profit Summary Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200 text-xs">
            <div class="bg-white p-3 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 uppercase font-semibold text-[11px] block">Total Consignor Cost (Payable):</span>
                <strong class="text-lg text-slate-900 font-extrabold block mt-0.5">&#8369;{{ number_format($grandConsignorTotal, 2) }}</strong>
                <span class="text-[10px] text-slate-400">Total liability owed to {{ $selectedConsignor?->name ?? 'supplier' }}</span>
            </div>
            <div class="bg-white p-3 rounded-xl border border-slate-200/80">
                <span class="text-slate-500 uppercase font-semibold text-[11px] block">Total Hospital Gross Billed:</span>
                <strong class="text-lg text-slate-900 font-extrabold block mt-0.5">&#8369;{{ number_format($grandHospitalSales, 2) }}</strong>
                <span class="text-[10px] text-slate-400">Cash receipts + charge slips billed</span>
            </div>
            <div class="bg-emerald-50/60 p-3 rounded-xl border border-emerald-200/80">
                <span class="text-emerald-700 uppercase font-semibold text-[11px] block">Hospital Retained Markup:</span>
                <strong class="text-lg text-emerald-700 font-black block mt-0.5">&#8369;{{ number_format($grandHospitalProfit, 2) }}</strong>
                <span class="text-[10px] text-emerald-600 font-medium">Net profit margin retained by RMPH</span>
            </div>
        </div>

        <!-- Official Signatures matching Capiz Provincial Government Format -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8 text-xs text-center">
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
