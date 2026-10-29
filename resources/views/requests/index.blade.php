@extends('layouts.app')

@section('title', 'Requisition & Issuance Archive')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-sky-600 font-bold mb-1">
                <span>RMPH SUPPLY MANAGEMENT</span>
                <span>&bull;</span>
                <span>REQUISITION MONITORING</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900">Requisitions &amp; Issuances Monitor</h1>
            <p class="text-xs sm:text-sm text-slate-500">Monitor who requested supplies, verify department requisition slips, and track stock issuances.</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('requests.portal') }}" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm shadow-sky-600/20 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Requisition Request</span>
            </a>
        </div>
    </div>

    <!-- Status Metric Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-slate-500 font-semibold uppercase">Total Requisitions</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">{{ count($batches) }}</div>
            <span class="text-[10px] text-slate-400">Total batches recorded</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/40 shadow-xs">
            <span class="text-xs text-amber-800 font-semibold uppercase">Pending Requests</span>
            <div class="text-xl sm:text-2xl font-bold text-amber-700 mt-1">{{ $totalPendingCount }}</div>
            <span class="text-[10px] text-amber-600">Awaiting supply issuance</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/40 shadow-xs">
            <span class="text-xs text-emerald-800 font-semibold uppercase">Issued &amp; Approved</span>
            <div class="text-xl sm:text-2xl font-bold text-emerald-700 mt-1">{{ $totalApprovedCount }}</div>
            <span class="text-[10px] text-emerald-600">Successfully released</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs text-sky-700 font-semibold uppercase">Requesting Wards</span>
            <div class="text-xl sm:text-2xl font-bold text-sky-700 mt-1">{{ $departments->count() }}</div>
            <span class="text-[10px] text-slate-400">Hospital departments</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('requests.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Requester, Department, RIS #, Item..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <select name="department" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Departments &amp; Wards</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->name }}" {{ request('department') == $d->name ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved (Issued)</option>
                    <option value="Denied" {{ request('status') == 'Denied' ? 'selected' : '' }}>Denied</option>
                </select>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl px-4 py-2.5 transition">
                    Filter
                </button>
                <a href="{{ route('requests.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Master Requisitions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 sticky top-0">
                    <tr>
                        <th class="py-3 px-4">RIS Control #</th>
                        <th class="py-3 px-4">Department &amp; Requester</th>
                        <th class="py-3 px-4 hidden md:table-cell">Purpose / Justification</th>
                        <th class="py-3 px-4">Items Requested</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 hidden lg:table-cell">Date &amp; Issued By</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-slate-50/80 transition {{ $batch['is_emergency'] ? 'bg-amber-50/30' : '' }}">
                        <!-- Batch ID -->
                        <td class="py-3.5 px-4 font-mono font-bold text-sky-700 whitespace-nowrap">
                            {{ $batch['batch_id'] }}
                            @if($batch['is_emergency'])
                                <span class="block text-[9px] font-black uppercase text-amber-700 tracking-wider">EMERGENCY</span>
                            @endif
                        </td>

                        <!-- Department & Requested By (Who requested) -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 text-sm leading-tight">{{ $batch['department_name'] }}</div>
                            <div class="text-xs text-sky-800 font-semibold flex items-center space-x-1 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-sky-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Requested by: <strong>{{ $batch['requested_by'] }}</strong></span>
                            </div>
                        </td>

                        <!-- Purpose -->
                        <td class="py-3.5 px-4 text-slate-600 hidden md:table-cell max-w-xs">
                            <span class="line-clamp-2">{{ $batch['purpose'] }}</span>
                        </td>

                        <!-- Items Requested -->
                        <td class="py-3.5 px-4">
                            <div class="space-y-1 max-w-xs">
                                @foreach($batch['items']->take(3) as $it)
                                    <div class="text-[11px] text-slate-700 flex items-center justify-between gap-2">
                                        <span class="truncate font-medium">{{ $it->product?->name }}</span>
                                        <span class="font-mono font-bold text-slate-900 flex-shrink-0">
                                            {{ $it->quantity_issued > 0 ? $it->quantity_issued : $it->quantity }} {{ $it->product?->unit_of_measure }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($batch['items']->count() > 3)
                                    <div class="text-[10px] text-slate-400 italic">+{{ $batch['items']->count() - 3 }} more item(s)</div>
                                @endif
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($batch['status'] === 'Pending')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    PENDING APPROVAL
                                </span>
                            @elseif($batch['status'] === 'Approved')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    ISSUED
                                </span>
                            @elseif($batch['status'] === 'Denied')
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    DENIED
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ strtoupper($batch['status']) }}
                                </span>
                            @endif
                        </td>

                        <!-- Date & Issued By -->
                        <td class="py-3.5 px-4 text-slate-600 hidden lg:table-cell whitespace-nowrap">
                            <div class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($batch['created_at'])->format('M d, Y h:i A') }}</div>
                            @if($batch['issued_by'])
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    Issued by: <strong class="text-slate-700">{{ $batch['issued_by'] }}</strong>
                                </div>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1.5">
                            @if($batch['status'] === 'Pending')
                                <form method="POST" action="{{ route('requests.approve', $batch['batch_id']) }}" class="inline" onsubmit="return confirm('Approve and issue supplies for {{ $batch['department_name'] }}? Stock will be deducted automatically.');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                                        Approve &amp; Issue
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('requests.deny', $batch['batch_id']) }}" class="inline" onsubmit="return confirm('Deny this requisition request?');">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 bg-rose-100 hover:bg-rose-200 text-rose-800 rounded-lg text-xs font-bold transition">
                                        Deny
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('requests.print', $batch['batch_id']) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-sky-50 hover:text-sky-700 text-slate-700 rounded-lg text-xs font-semibold transition inline-flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                <span>Print RIS</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="font-semibold text-slate-600">No requisitions found matching your filter</p>
                            <p class="text-xs text-slate-400 mt-1">Submit a new request from the portal or clear filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
