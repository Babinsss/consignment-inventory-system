@extends('layouts.app')

@section('title', 'Schedule of Consumed Goods (ScCG) - HECS')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Top Action & Filter Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-sky-700 uppercase tracking-widest mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-sky-600"></span>
                <span>HECS Patient Schedule • Capiz Provincial Government</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Schedule of Consumed Goods (ScCG)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Detailed itemized patient issuance schedule with Official Receipt / Charge Slip references.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <button type="button" onclick="window.print()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition space-x-2">
                <span>🖨️</span>
                <span>Print ScCG Schedule</span>
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs no-print">
        <form method="GET" action="{{ route('hospital.sccg') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-4">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Consignor / Supplier</label>
                <select name="consignor_id" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-sky-500 font-medium">
                    <option value="">All Consignors</option>
                    @foreach($consignors as $c)
                        <option value="{{ $c->id }}" {{ $selectedConsignor?->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Transaction Category</label>
                <select name="type" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-sky-500 font-medium">
                    <option value="ALL" {{ $typeFilter === 'ALL' ? 'selected' : '' }}>All (Charged & Cash)</option>
                    <option value="CHARGE" {{ $typeFilter === 'CHARGE' ? 'selected' : '' }}>Charge Transactions</option>
                    <option value="CASH" {{ $typeFilter === 'CASH' ? 'selected' : '' }}>Cash Transactions</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Month</label>
                <select name="month" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-sky-500 font-medium">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="sm:col-span-1">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Year</label>
                <input type="number" name="year" value="{{ $year }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-sky-500 font-mono font-medium">
            </div>

            <div class="sm:col-span-2 flex items-end">
                <button type="submit" class="w-full py-2 px-4 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs transition shadow-sm shadow-sky-600/20">
                    Apply Filter
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
                    SCHEDULE OF CONSUMED GOODS ({{ $typeFilter === 'ALL' ? 'CHARGED & CASH TRANSACTIONS' : ($typeFilter === 'CHARGE' ? 'CHARGED TRANSACTIONS' : 'CASH TRANSACTIONS') }})
                </span>
            </div>
        </div>

        <!-- Meta Info -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-700 border-b border-slate-100 pb-3 gap-2">
            <div>
                <p><strong>StCG No.:</strong> ___________________________</p>
                <p><strong>Consignor:</strong> <span class="font-bold text-sky-900">{{ $selectedConsignor?->name ?? 'ALL CONSIGNORS' }}</span></p>
            </div>
            <div class="sm:text-right">
                <p><strong>Period:</strong> <span class="font-bold">{{ $periodLabel }}</span></p>
                <p><strong>Total Items in Schedule:</strong> <span class="font-bold text-slate-900">{{ $items->total() }}</span></p>
            </div>
        </div>

        <!-- Mobile Card View (shown only on small screens) -->
        <div class="block md:hidden space-y-3 no-print">
            @forelse($items as $it)
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center space-x-1.5 mb-1">
                            @if(strtolower($it->sale->transaction_type) === 'walk_in_sale' || strtolower($it->sale->payment_method) === 'cash')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-800">CASH</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-800">CHARGE</span>
                            @endif
                            <span class="text-[10px] text-slate-500 font-medium">{{ $it->sale->sale_date->format('M d, Y') }}</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900">{{ $it->product->name }}</h4>
                        <span class="font-mono text-[10px] text-slate-400">{{ $it->product->sku }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 block">Issued Qty:</span>
                        <span class="text-sm font-extrabold text-slate-900">{{ $it->quantity }}</span>
                    </div>
                </div>

                <div class="bg-white p-2.5 rounded-lg border border-slate-200/80 text-[11px] space-y-1">
                    <div class="flex justify-between text-slate-600">
                        <span>Issued to (Patient):</span>
                        <strong class="text-slate-900">{{ $it->sale->patient_name ?: $it->sale->department_name ?: 'Walk-in' }}</strong>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>OR / Slip Ref:</span>
                        <span class="font-mono font-bold text-slate-700">{{ $it->sale->or_number ?: $it->sale->charge_slip_no ?: $it->sale->sale_number }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Consignor Total:</span>
                        <span class="font-bold text-sky-800">&#8369;{{ number_format($it->subtotal_cost, 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block text-[10px]">Patient Billed Total:</span>
                        <span class="font-bold text-slate-900">&#8369;{{ number_format($it->subtotal_amount, 2) }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 text-xs italic bg-slate-50 rounded-xl border border-dashed border-slate-200">
                No schedule records found matching your filters.
            </div>
            @endforelse
        </div>

        <!-- Desktop ScCG Table (Also used for Print) -->
        <div class="hidden md:block has-scroll-hint custom-scroll overflow-x-auto border border-slate-300 rounded-xl">
            <table class="w-full text-left text-[11px]">
                <thead class="bg-slate-100 text-slate-800 font-bold border-b border-slate-300 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-2">Type</th>
                        <th class="py-2.5 px-3">Item Code</th>
                        <th class="py-2.5 px-3 min-w-[200px]">Item Name</th>
                        <th class="py-2.5 px-3 text-right">Consignor Price</th>
                        <th class="py-2.5 px-2 text-right">Selling Price</th>
                        <th class="py-2.5 px-2 text-center">Qty Issued</th>
                        <th class="py-2.5 px-3 text-right">Total Sales</th>
                        <th class="py-2.5 px-3 min-w-[150px]">Issued to (Patient)</th>
                        <th class="py-2.5 px-3 font-mono">OR / Slip Ref</th>
                        <th class="py-2.5 px-3 text-right">Total Consignor Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($items as $it)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2 px-3 text-slate-600 whitespace-nowrap">{{ $it->sale->sale_date->format('m/d/Y') }}</td>
                        <td class="py-2 px-2 whitespace-nowrap">
                            @if(strtolower($it->sale->transaction_type) === 'walk_in_sale' || strtolower($it->sale->payment_method) === 'cash')
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-800">CASH</span>
                            @else
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-100 text-sky-800">CHARGE</span>
                            @endif
                        </td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">{{ $it->product->sku }}</td>
                        <td class="py-2 px-3 font-medium text-slate-800">{{ $it->product->name }}</td>
                        <td class="py-2 px-3 text-right text-slate-600 whitespace-nowrap">&#8369;{{ number_format($it->unit_cost, 2) }}</td>
                        <td class="py-2 px-2 text-right text-slate-700 whitespace-nowrap">&#8369;{{ number_format($it->unit_price, 2) }}</td>
                        <td class="py-2 px-2 text-center font-bold text-slate-900">{{ $it->quantity }}</td>
                        <td class="py-2 px-3 text-right font-bold text-slate-900 whitespace-nowrap">&#8369;{{ number_format($it->subtotal_amount, 2) }}</td>
                        <td class="py-2 px-3 font-bold text-slate-800">{{ $it->sale->patient_name ?: $it->sale->department_name ?: 'Walk-in' }}</td>
                        <td class="py-2 px-3 font-mono text-slate-600 whitespace-nowrap">{{ $it->sale->or_number ?: $it->sale->charge_slip_no ?: $it->sale->sale_number }}</td>
                        <td class="py-2 px-3 text-right font-bold text-sky-800 whitespace-nowrap">&#8369;{{ number_format($it->subtotal_cost, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-8 text-center text-slate-400 italic">No schedule records found matching your filters.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300 text-xs">
                    <tr>
                        <td colspan="6" class="py-3 px-3 uppercase text-right">TOTAL IN SCHEDULE:</td>
                        <td class="py-3 px-2 text-center font-extrabold">{{ number_format($items->sum('quantity')) }}</td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900">&#8369;{{ number_format($items->sum('subtotal_amount'), 2) }}</td>
                        <td colspan="2"></td>
                        <td class="py-3 px-3 text-right font-black text-sky-900 whitespace-nowrap">&#8369;{{ number_format($items->sum('subtotal_cost'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($items->hasPages())
        <div class="p-3 border-t border-slate-100 no-print">
            {{ $items->links() }}
        </div>
        @endif

        <!-- Signatures Matching Official Format -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-12 text-xs text-center">
            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Prepared by:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">JOEMAR A. DADIVAS, RN, CNN, MAN</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Nurse 1 / Dialysis Unit Nurse Supervisor</div>
            </div>

            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Certified Correct:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">FLORENCIO O. LUCHING, JR, MD</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Acting Chief of Hospital II</div>
            </div>
        </div>

    </div>

</div>
@endsection
