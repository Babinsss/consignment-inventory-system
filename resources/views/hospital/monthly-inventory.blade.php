@extends('layouts.app')

@section('title', 'Monthly Consignment Inventory Report - HECS')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Top Action & Filter Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-emerald-700 uppercase tracking-widest mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-600"></span>
                <span>HECS Monthly Count • Capiz Provincial Government</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Monthly Consignment Inventory Report</h1>
            <p class="text-xs sm:text-sm text-slate-500">Official 13-column stock balance, physical count, and vendor collectibles reconciliation.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <button type="button" onclick="window.print()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition space-x-2">
                <span>🖨️</span>
                <span>Print Monthly Inventory</span>
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs no-print">
        <form method="GET" action="{{ route('hospital.monthly-inventory') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Consignor / Supplier</label>
                <select name="consignor_id" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-emerald-500 font-medium">
                    @foreach($consignors as $c)
                        <option value="{{ $c->id }}" {{ $selectedConsignor?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Month</label>
                <select name="month" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-emerald-500 font-medium">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-emerald-500 font-mono font-medium">
            </div>

            <div class="sm:col-span-2 flex items-end">
                <button type="submit" class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm shadow-emerald-600/20">
                    Generate Sheet
                </button>
            </div>
        </form>
    </div>

    <!-- Official Document Card -->
    <div class="bg-white rounded-2xl p-5 sm:p-8 border border-slate-200 shadow-xs space-y-6">

        <!-- Header -->
        <div class="text-center space-y-1 border-b border-slate-200 pb-5">
            <p class="text-[11px] uppercase tracking-widest text-slate-500 font-semibold">Republic of the Philippines</p>
            <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">CAPIZ PROVINCIAL GOVERNMENT</h2>
            <h3 class="text-sm sm:text-base font-extrabold text-sky-800 uppercase tracking-wide">HOSPITAL EXTENSIVE CONSIGNMENT SYSTEM (HECS)</h3>
            <h1 class="text-base sm:text-lg font-black text-slate-900 uppercase">ROXAS MEMORIAL PROVINCIAL HOSPITAL</h1>
            <div class="pt-2">
                <span class="inline-block px-4 py-1 bg-slate-100 text-slate-800 rounded-lg font-bold text-xs sm:text-sm uppercase tracking-wider border border-slate-300">
                    MONTHLY INVENTORY REPORT
                </span>
            </div>
        </div>

        <!-- Meta -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-700 border-b border-slate-100 pb-3 gap-2">
            <div>
                <p><strong>Consignor:</strong> <span class="font-bold text-sky-900">{{ $selectedConsignor?->name ?? 'ALL CONSIGNORS' }}</span></p>
            </div>
            <div class="sm:text-right">
                <p><strong>StCG No.:</strong> ___________________________</p>
                <p><strong>Period:</strong> <span class="font-bold">{{ $periodLabel }}</span></p>
            </div>
        </div>

        <!-- Quick Summary Metrics Ribbon -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
            <div>
                <span class="text-slate-400 uppercase text-[10px] font-bold block">Stock Receipts</span>
                <span class="text-base font-extrabold text-emerald-700">+{{ number_format($report->sum('receipts')) }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase text-[10px] font-bold block">Patient Issuances</span>
                <span class="text-base font-extrabold text-sky-700">-{{ number_format($report->sum('issuance')) }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase text-[10px] font-bold block">Ending Physical Count</span>
                <span class="text-base font-extrabold text-slate-900">{{ number_format($report->sum('physical_count')) }}</span>
            </div>
            <div>
                <span class="text-slate-400 uppercase text-[10px] font-bold block">Total Collectibles Due</span>
                <span class="text-base font-black text-purple-800">&#8369;{{ number_format($report->sum('collectibles'), 2) }}</span>
            </div>
        </div>

        <!-- Mobile Card View (shown only on small screens) -->
        <div class="block md:hidden space-y-3 no-print">
            @forelse($report as $r)
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-mono text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60">{{ $r['code'] }}</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-1">{{ $r['name'] }}</h4>
                    </div>
                    <span class="text-xs font-bold text-slate-700">&#8369;{{ number_format($r['cost'], 2) }}</span>
                </div>

                <div class="grid grid-cols-4 gap-1.5 text-center text-[10px] bg-white p-2.5 rounded-lg border border-slate-200/80">
                    <div>
                        <span class="text-slate-400 block">Beg</span>
                        <span class="font-bold text-slate-700">{{ $r['beg_bal'] }}</span>
                    </div>
                    <div>
                        <span class="text-emerald-600 block">+In</span>
                        <span class="font-bold text-emerald-700">{{ $r['receipts'] ?: 0 }}</span>
                    </div>
                    <div>
                        <span class="text-sky-600 block">-Out</span>
                        <span class="font-bold text-sky-700">{{ $r['issuance'] ?: 0 }}</span>
                    </div>
                    <div>
                        <span class="text-slate-900 block font-bold">Physical</span>
                        <span class="font-extrabold text-slate-900">{{ $r['physical_count'] }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/60">
                    <div>
                        <span class="text-[10px] text-slate-400 block">Total Sales Billed:</span>
                        <span class="font-semibold text-slate-800">&#8369;{{ number_format($r['total_sales'], 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block">Collectibles Due:</span>
                        <span class="font-black text-sky-800">&#8369;{{ number_format($r['collectibles'], 2) }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                No consignment products mapped to this supplier.
            </div>
            @endforelse
        </div>

        <!-- 13 Column Table matching Excel Template (Desktop & Print) -->
        <div class="hidden md:block has-scroll-hint custom-scroll overflow-x-auto border border-slate-300 rounded-xl">
            <table class="w-full text-left text-[11px]">
                <thead class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300 text-center uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3 text-left">Item Code</th>
                        <th class="py-2.5 px-3 text-left min-w-[200px]">Item Name</th>
                        <th class="py-2.5 px-2 text-right">Consignor Price</th>
                        <th class="py-2.5 px-2 text-center">Beg. Bal</th>
                        <th class="py-2.5 px-2 text-center">Stock Receipt</th>
                        <th class="py-2.5 px-2 text-center">Pull Out</th>
                        <th class="py-2.5 px-2 text-center">Issuance</th>
                        <th class="py-2.5 px-2 text-center">Returns</th>
                        <th class="py-2.5 px-2 text-center font-extrabold bg-slate-200/50">Ending Bal</th>
                        <th class="py-2.5 px-2 text-center font-extrabold bg-slate-200/50">Physical Count</th>
                        <th class="py-2.5 px-3 text-right">Total Sales</th>
                        <th class="py-2.5 px-3 text-right font-black text-sky-900">Total Collectibles</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs">
                    @forelse($report as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $r['code'] }}</td>
                        <td class="py-2.5 px-3 font-medium text-slate-800">{{ $r['name'] }}</td>
                        <td class="py-2.5 px-2 text-right text-slate-700">&#8369;{{ number_format($r['cost'], 2) }}</td>
                        <td class="py-2.5 px-2 text-center text-slate-600">{{ $r['beg_bal'] }}</td>
                        <td class="py-2.5 px-2 text-center font-semibold text-emerald-700">{{ $r['receipts'] ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-center text-slate-400">{{ $r['pull_out'] ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-center font-bold text-sky-700">{{ $r['issuance'] ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-center text-slate-400">{{ $r['returns'] ?: '-' }}</td>
                        <td class="py-2.5 px-2 text-center font-extrabold text-slate-900 bg-slate-50/80">{{ $r['ending_bal'] }}</td>
                        <td class="py-2.5 px-2 text-center font-extrabold text-slate-900 bg-slate-50/80">{{ $r['physical_count'] }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900 whitespace-nowrap">&#8369;{{ number_format($r['total_sales'], 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-extrabold text-sky-900 whitespace-nowrap">&#8369;{{ number_format($r['collectibles'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-8 text-center text-slate-400 italic">No consignment products mapped to this supplier.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300 text-xs">
                    <tr>
                        <td colspan="4" class="py-3 px-3 uppercase text-right">TOTALS:</td>
                        <td class="py-3 px-2 text-center font-semibold text-emerald-800">{{ number_format($report->sum('receipts')) }}</td>
                        <td class="py-3 px-2 text-center">-</td>
                        <td class="py-3 px-2 text-center font-bold text-sky-800">{{ number_format($report->sum('issuance')) }}</td>
                        <td class="py-3 px-2 text-center">-</td>
                        <td class="py-3 px-2 text-center font-extrabold">{{ number_format($report->sum('ending_bal')) }}</td>
                        <td class="py-3 px-2 text-center font-extrabold">{{ number_format($report->sum('physical_count')) }}</td>
                        <td class="py-3 px-3 text-right font-bold">&#8369;{{ number_format($report->sum('total_sales'), 2) }}</td>
                        <td class="py-3 px-3 text-right font-black text-sky-900 whitespace-nowrap">&#8369;{{ number_format($report->sum('collectibles'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Official Signatures matching Capiz Provincial Government Format -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-xs text-center">
            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Inventory Conducted by:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">ALIANA MARIE A. DULA</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Administrative Aide III</div>
            </div>

            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Certified Correct:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">FLORENCIO O. LUCHING, JR, MD</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Acting Chief of Hospital II</div>
            </div>

            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Conformed:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">JUDY LYN G. BERMOY</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Consignor Representative</div>
            </div>

            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Certified by:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">FLORENCIO O. LUCHING, JR., MD, FPCS</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Chief of Hospital II</div>
            </div>
        </div>

    </div>

</div>
@endsection
