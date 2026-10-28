@extends('layouts.app')

@section('title', 'Suppliers & Distributors')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-purple-700 uppercase tracking-widest mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-purple-600"></span>
                <span>Vendor Directory & Consignor Partnerships</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Medical Suppliers & Pharmaceutical Distributors</h1>
            <p class="text-xs sm:text-sm text-slate-500">Manage vendor directories, consignment agreements, credit terms, and payment ledgers.</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="inline-flex items-center justify-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-sm shadow-sky-600/20 transition space-x-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Register Supplier</span>
        </a>
    </div>

    <!-- Suppliers Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center space-x-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier name, code, contact..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 text-slate-800 focus:ring-2 focus:ring-purple-500 font-medium">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition shrink-0">Search</button>
            </form>
            <span class="text-xs text-slate-500 font-medium shrink-0">Total Suppliers: <strong class="text-slate-900">{{ $suppliers->total() }}</strong></span>
        </div>

        <div class="has-scroll-hint custom-scroll overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 font-bold">
                    <tr>
                        <th class="py-3 px-4">Supplier & Code</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Contact Info</th>
                        <th class="py-3 px-4">Terms</th>
                        <th class="py-3 px-4 text-center">Deliveries</th>
                        <th class="py-3 px-4 text-right hidden md:table-cell">Total Purchased</th>
                        <th class="py-3 px-4 text-right hidden lg:table-cell">Total Paid</th>
                        <th class="py-3 px-4 text-right">Balance Due</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($suppliers as $s)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <a href="{{ route('suppliers.show', $s) }}" class="text-sky-600 hover:underline">
                                {{ $s->name }}
                            </a>
                            <span class="block text-[10px] font-mono text-slate-400 font-normal">{{ $s->code }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 hidden sm:table-cell">
                            {{ $s->contact_person ?: '—' }}
                            <span class="block text-[10px] text-slate-400">{{ $s->phone ?: $s->email }}</span>
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            @if($s->payment_terms === 'Consignment')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">Consignment</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">{{ $s->payment_terms }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-700">
                            {{ $s->deliveries_count }}
                        </td>
                        <td class="py-3 px-4 text-right font-medium text-slate-900 whitespace-nowrap hidden md:table-cell">
                            &#8369;{{ number_format($s->total_purchased, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right text-emerald-700 font-medium whitespace-nowrap hidden lg:table-cell">
                            &#8369;{{ number_format($s->total_paid, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold {{ $s->balance_due > 0 ? 'text-rose-600' : 'text-slate-400' }} whitespace-nowrap">
                            &#8369;{{ number_format($s->balance_due, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $s->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                {{ ucfirst($s->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                            <a href="{{ route('suppliers.show', $s) }}" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">View</a>
                            <a href="{{ route('suppliers.edit', $s) }}" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400 italic">No suppliers registered.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
