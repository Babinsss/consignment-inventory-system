@extends('layouts.app')

@section('title', 'FEFO Expiration Audit Report')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">FEFO Expiration Date Audit</h1>
            <p class="text-sm text-slate-500">First Expire, First Out (FEFO) compliance monitor to prevent medication expiry losses.</p>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition border border-slate-300">
                🖨️ Print Expiry Report
            </button>
        </div>
    </div>

    <!-- Alert KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('reports.expiry', ['days' => 0]) }}" class="bg-red-50/70 p-4 rounded-xl border border-red-200 shadow-xs hover:border-red-300 transition">
            <span class="text-xs text-red-800 font-semibold uppercase">Already Expired Batches</span>
            <div class="text-3xl font-extrabold text-red-700 mt-1">{{ $expiredCount }}</div>
            <span class="text-[11px] text-red-600 block mt-1">Requires immediate disposal / condemn</span>
        </a>

        <a href="{{ route('reports.expiry', ['days' => 30]) }}" class="bg-amber-50/70 p-4 rounded-xl border border-amber-200 shadow-xs hover:border-amber-300 transition">
            <span class="text-xs text-amber-800 font-semibold uppercase">Critical: Expiring within 30 Days</span>
            <div class="text-3xl font-extrabold text-amber-700 mt-1">{{ $criticalCount }}</div>
            <span class="text-[11px] text-amber-600 block mt-1">Priority for dispensing queue</span>
        </a>

        <a href="{{ route('reports.expiry', ['days' => 90]) }}" class="bg-yellow-50/70 p-4 rounded-xl border border-yellow-200 shadow-xs hover:border-yellow-300 transition">
            <span class="text-xs text-yellow-800 font-semibold uppercase">Watchlist: Expiring within 90 Days</span>
            <div class="text-3xl font-extrabold text-yellow-800 mt-1">{{ $warningCount }}</div>
            <span class="text-[11px] text-yellow-700 block mt-1">Monitor stock rotation</span>
        </a>
    </div>

    <!-- Filter Strip -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3 text-xs">
        <span class="font-bold text-slate-700">Filter Expiry Window:</span>
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.expiry', ['days' => 30]) }}" class="px-3 py-1.5 rounded-lg {{ $daysThreshold === 30 ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">Next 30 Days</a>
            <a href="{{ route('reports.expiry', ['days' => 60]) }}" class="px-3 py-1.5 rounded-lg {{ $daysThreshold === 60 ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">Next 60 Days</a>
            <a href="{{ route('reports.expiry', ['days' => 90]) }}" class="px-3 py-1.5 rounded-lg {{ $daysThreshold === 90 ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">Next 90 Days</a>
            <a href="{{ route('reports.expiry', ['days' => 180]) }}" class="px-3 py-1.5 rounded-lg {{ $daysThreshold === 180 ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">Next 6 Months</a>
            <a href="{{ route('reports.expiry', ['days' => 365]) }}" class="px-3 py-1.5 rounded-lg {{ $daysThreshold === 365 ? 'bg-amber-600 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">All (Next 1 Year)</a>
        </div>
    </div>

    <!-- Expiry Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Medication / Supply</th>
                        <th class="py-3 px-4">Batch / Lot #</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Supplier</th>
                        <th class="py-3 px-4">Expiry Date</th>
                        <th class="py-3 px-4">Countdown Status</th>
                        <th class="py-3 px-4 text-right">Units at Risk</th>
                        <th class="py-3 px-4 text-right">Unit Cost</th>
                        <th class="py-3 px-4 text-right">Valuation at Risk</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $b)
                    <tr class="hover:bg-slate-50 {{ $b->is_expired ? 'bg-red-50/40' : ($b->days_until_expiry <= 30 ? 'bg-amber-50/30' : '') }}">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <a href="{{ route('products.show', $b->product) }}" class="text-sky-600 hover:underline">
                                {{ $b->product->name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $b->product->sku }}</span>
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-800">
                            {{ $b->batch_number }}
                        </td>
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                            {{ $b->product->category->name }}
                        </td>
                        <td class="py-3 px-4 text-slate-600">
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
                        <td class="py-3 px-4 text-right text-slate-600 whitespace-nowrap">
                            ₱{{ number_format($b->unit_purchase_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-rose-600 whitespace-nowrap">
                            ₱{{ number_format($b->quantity_remaining * $b->unit_purchase_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('products.show', $b->product) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">
                                Manage &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-400">
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
