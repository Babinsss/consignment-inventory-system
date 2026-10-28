@extends('layouts.app')

@section('title', 'FEFO Expiration Audit Report')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-amber-700 uppercase tracking-widest mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-600"></span>
                <span>FEFO Inventory Safety Protocol</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">FEFO Expiration Date Audit</h1>
            <p class="text-xs sm:text-sm text-slate-500">First Expire, First Out (FEFO) compliance monitor to prevent medication expiry losses.</p>
        </div>
        <div class="flex items-center space-x-2 shrink-0">
            <button type="button" onclick="window.print()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition border border-slate-300 space-x-1.5">
                <span>🖨️</span>
                <span>Print Expiry Report</span>
            </button>
        </div>
    </div>

    <!-- Alert KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('reports.expiry', ['days' => 0]) }}" class="bg-red-50/80 p-5 rounded-2xl border border-red-200 shadow-xs hover:border-red-300 transition group">
            <span class="text-xs text-red-800 font-bold uppercase tracking-wider block">Already Expired Batches</span>
            <div class="text-3xl font-black text-red-700 mt-2">{{ $expiredCount }}</div>
            <span class="text-xs text-red-600 block mt-1 font-medium group-hover:underline">Requires immediate disposal / condemn &rarr;</span>
        </a>

        <a href="{{ route('reports.expiry', ['days' => 30]) }}" class="bg-amber-50/80 p-5 rounded-2xl border border-amber-200 shadow-xs hover:border-amber-300 transition group">
            <span class="text-xs text-amber-800 font-bold uppercase tracking-wider block">Critical: Expiring within 30 Days</span>
            <div class="text-3xl font-black text-amber-700 mt-2">{{ $criticalCount }}</div>
            <span class="text-xs text-amber-600 block mt-1 font-medium group-hover:underline">Priority for dispensing queue &rarr;</span>
        </a>

        <a href="{{ route('reports.expiry', ['days' => 90]) }}" class="bg-yellow-50/80 p-5 rounded-2xl border border-yellow-200 shadow-xs hover:border-yellow-300 transition group">
            <span class="text-xs text-yellow-800 font-bold uppercase tracking-wider block">Watchlist: Expiring within 90 Days</span>
            <div class="text-3xl font-black text-yellow-800 mt-2">{{ $warningCount }}</div>
            <span class="text-xs text-yellow-700 block mt-1 font-medium group-hover:underline">Monitor stock rotation &rarr;</span>
        </a>
    </div>

    <!-- Filter Strip -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Filter Expiry Window:</span>
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('reports.expiry', ['days' => 30]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition text-xs {{ $daysThreshold === 30 ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">Next 30 Days</a>
            <a href="{{ route('reports.expiry', ['days' => 60]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition text-xs {{ $daysThreshold === 60 ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">Next 60 Days</a>
            <a href="{{ route('reports.expiry', ['days' => 90]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition text-xs {{ $daysThreshold === 90 ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">Next 90 Days</a>
            <a href="{{ route('reports.expiry', ['days' => 180]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition text-xs {{ $daysThreshold === 180 ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">Next 6 Months</a>
            <a href="{{ route('reports.expiry', ['days' => 365]) }}" class="px-3 py-1.5 rounded-xl whitespace-nowrap transition text-xs {{ $daysThreshold === 365 ? 'bg-amber-600 text-white font-bold shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">All (Next 1 Year)</a>
        </div>
    </div>

    <!-- Expiry Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="has-scroll-hint custom-scroll overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 font-bold">
                    <tr>
                        <th class="py-3 px-4">Medication / Supply</th>
                        <th class="py-3 px-4">Batch / Lot #</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Category</th>
                        <th class="py-3 px-4 hidden md:table-cell">Supplier</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4">Countdown Status</th>
                        <th class="py-3 px-4 text-right">Units at Risk</th>
                        <th class="py-3 px-4 text-right hidden lg:table-cell">Unit Cost</th>
                        <th class="py-3 px-4 text-right">Valuation at Risk</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $b)
                    <tr class="hover:bg-slate-50 transition {{ $b->is_expired ? 'bg-red-50/40' : ($b->days_until_expiry <= 30 ? 'bg-amber-50/30' : '') }}">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <a href="{{ route('products.show', $b->product) }}" class="text-sky-600 hover:underline">
                                {{ $b->product->name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $b->product->sku }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">
                            {{ $b->batch_number }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap hidden sm:table-cell">
                            {{ $b->product->category->name }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 hidden md:table-cell">
                            {{ $b->delivery && $b->delivery->supplier ? Str::limit($b->delivery->supplier->name, 18) : '—' }}
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-900 whitespace-nowrap">
                            {{ $b->expiry_date->format('M d, Y') }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($b->days_until_expiry < 0)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                    EXPIRED ({{ abs($b->days_until_expiry) }}d ago)
                                </span>
                            @elseif($b->days_until_expiry <= 30)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                    {{ $b->days_until_expiry }} days left
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800">
                                    {{ $b->days_until_expiry }} days left
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right font-extrabold text-slate-900 whitespace-nowrap">
                            {{ $b->quantity_remaining }} <span class="text-[10px] text-slate-400 font-normal">{{ $b->product->unit_of_measure }}</span>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-600 whitespace-nowrap hidden lg:table-cell">
                            &#8369;{{ number_format($b->unit_purchase_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-black text-rose-600 whitespace-nowrap">
                            &#8369;{{ number_format($b->quantity_remaining * $b->unit_purchase_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('products.show', $b->product) }}" class="text-xs font-bold text-sky-600 hover:text-sky-800">
                                Manage &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-400 italic">
                            No batches found expiring within {{ $daysThreshold }} days. Good inventory rotation!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
