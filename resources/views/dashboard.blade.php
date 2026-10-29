@extends('layouts.app')

@section('title', 'RMPH Supply Management & Requisitions')

@section('content')
<div class="space-y-6" x-data="{ criticalModal: false, approveModal: null }">

    <!-- Top Alert Notification Bar if there are low stock items or pending requests -->
    @if($lowStockCount > 0 || $pendingCount > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($pendingCount > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between shadow-xs gap-3">
            <div class="flex items-center space-x-3">
                <span class="p-2 bg-amber-100 rounded-xl text-amber-700 flex-shrink-0 animate-bounce">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <div>
                    <h4 class="text-sm font-bold text-amber-950">Action Needed: Pending Requisitions</h4>
                    <p class="text-xs text-amber-800">There are <strong>{{ $pendingCount }}</strong> department request batch(es) awaiting review and issuance.</p>
                </div>
            </div>
            <a href="#requests-timeline" class="text-xs font-semibold px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl transition flex-shrink-0 self-end sm:self-auto">Review Timeline &darr;</a>
        </div>
        @endif

        @if($lowStockCount > 0)
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between shadow-xs gap-3">
            <div class="flex items-center space-x-3">
                <span class="p-2 bg-rose-100 rounded-xl text-rose-700 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h4 class="text-sm font-bold text-rose-950">Critical Stock Warning</h4>
                    <p class="text-xs text-rose-800"><strong>{{ $lowStockCount }}</strong> supply item(s) are at or below minimum reorder thresholds.</p>
                </div>
            </div>
            <button type="button" @click="criticalModal = true" class="text-xs font-semibold px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition flex-shrink-0 self-end sm:self-auto">View Alert Items</button>
        </div>
        @endif
    </div>
    @endif

    <!-- Page Header & Live Status -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs text-sky-600 font-bold mb-1">
                <span>ROXAS MEMORIAL PROVINCIAL HOSPITAL</span>
                <span>&bull;</span>
                <span>SUPPLY MANAGEMENT &amp; ISSUANCE SYSTEM</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-slate-900">Hospital Supply Operations</h1>
            <p class="text-xs sm:text-sm text-slate-500">Track inward deliveries, monitor inventory levels, and process department supply requisitions.</p>
        </div>
        <div class="flex items-center space-x-2 self-start sm:self-auto flex-wrap gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Stock Flow Active
            </span>
            <a href="{{ route('requests.portal') }}" class="px-3.5 py-1.5 bg-sky-600 text-white hover:bg-sky-700 rounded-xl text-xs font-bold shadow-xs transition flex items-center space-x-1">
                <span>+ Submit Requisition</span>
            </a>
        </div>
    </div>

    <!-- Quick Operations Launchpad -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <a href="{{ route('requests.portal') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-sky-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-sky-600">Requisition Portal</span>
            <span class="text-[10px] text-slate-400">Request Supplies</span>
        </a>

        <a href="{{ route('deliveries.create') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-teal-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-teal-600">Track New Delivery</span>
            <span class="text-[10px] text-slate-400">Inward Stock Intake</span>
        </a>

        <a href="{{ route('products.index') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-indigo-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7h16m-8 4v6m-4-3h8"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-indigo-600">Stock Inventory</span>
            <span class="text-[10px] text-slate-400">Monitor Levels</span>
        </a>

        <a href="{{ route('requests.index') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-purple-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-purple-600">Requisitions Archive</span>
            <span class="text-[10px] text-slate-400">Who Requested</span>
        </a>

        <a href="{{ route('hospital.stock-card') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-emerald-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-emerald-600">Stock Card Ledger</span>
            <span class="text-[10px] text-slate-400">General Form 18</span>
        </a>

        <a href="{{ route('hospital.monthly-inventory') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-amber-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-amber-600">Physical Count</span>
            <span class="text-[10px] text-slate-400">Monthly Audit</span>
        </a>
    </div>

    <!-- 4 Main Supply Operations Bento KPI Cards (Matching RMPH-supply-management) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Card 1: Pending Requests -->
        <a href="#requests-timeline" class="bg-gradient-to-br from-sky-600 to-indigo-700 text-white rounded-2xl p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-sky-200 uppercase tracking-wider">Pending Requests</span>
                <span class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black">{{ $pendingCount }}</div>
            <div class="mt-3 pt-3 border-t border-white/20 text-xs text-sky-200 flex items-center justify-between">
                <span>Requires review &amp; issuance</span>
                <span class="font-bold underline">Review &rarr;</span>
            </div>
        </a>

        <!-- Card 2: Total Requisitions Issued -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Requisitions</span>
                <span class="w-9 h-9 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ number_format($totalRequisitionsCount) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
                <span>7-Day Activity: <strong class="text-slate-800">{{ array_sum($chartData) }}</strong> batches</span>
                <a href="{{ route('requests.index') }}" class="text-sky-600 font-bold hover:underline">Archive &rarr;</a>
            </div>
        </div>

        <!-- Card 3: Low Stock Alerts -->
        <div @click="criticalModal = true" class="bg-rose-50/70 border border-rose-200 rounded-2xl p-5 shadow-xs cursor-pointer hover:border-rose-400 transition flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-rose-700 uppercase tracking-wider">Low Stock Alerts</span>
                <span class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black text-rose-700">{{ $lowStockCount }}</div>
            <div class="mt-3 pt-3 border-t border-rose-200 text-xs text-rose-800 flex items-center justify-between">
                <span>Items at/below reorder level</span>
                <span class="font-bold underline">Inspect &rarr;</span>
            </div>
        </div>

        <!-- Card 4: Inward Deliveries Intake -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Deliveries Logged</span>
                <span class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="text-3xl font-black text-slate-900">{{ number_format($totalDeliveriesCount) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500 flex items-center justify-between">
                <span>This Month: <strong class="text-teal-700">{{ $deliveriesThisMonthCount }}</strong> shipments</span>
                <a href="{{ route('deliveries.index') }}" class="text-teal-600 font-bold hover:underline">Track &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Secondary Stock Monitoring Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <a href="{{ route('products.index', ['item_type' => 'medical_supply']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-sky-400 shadow-xs transition group">
            <div class="text-xs text-slate-500 group-hover:text-sky-600 font-medium">Medical Supplies</div>
            <div class="text-lg sm:text-xl font-bold text-slate-800 mt-1">{{ $totalMedicalSupplies }} <span class="text-xs font-normal text-slate-400">Items</span></div>
        </a>
        <a href="{{ route('products.index', ['item_type' => 'pharmacy']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-sky-400 shadow-xs transition group">
            <div class="text-xs text-slate-500 group-hover:text-sky-600 font-medium">Pharmacy Stocks</div>
            <div class="text-lg sm:text-xl font-bold text-slate-800 mt-1">{{ $totalPharmacyProducts }} <span class="text-xs font-normal text-slate-400">Medicines</span></div>
        </a>
        <a href="{{ route('products.index', ['inventory_type' => 'consignment']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-sky-400 shadow-xs transition group">
            <div class="text-xs text-slate-500 group-hover:text-sky-600 font-medium">Consignment Items</div>
            <div class="text-lg sm:text-xl font-bold text-slate-800 mt-1">{{ $totalConsignmentProducts }} <span class="text-xs font-normal text-slate-400">CSG Goods</span></div>
        </a>
        <a href="{{ route('suppliers.index') }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-sky-400 shadow-xs transition group">
            <div class="text-xs text-slate-500 group-hover:text-sky-600 font-medium">Consignors &amp; Suppliers</div>
            <div class="text-lg sm:text-xl font-bold text-slate-800 mt-1">{{ $totalSuppliers }} <span class="text-xs font-normal text-slate-400">Vendors</span></div>
        </a>
    </div>

    <!-- REQUEST PROCESSING TIMELINE SECTION (MATCHING RMPH-SUPPLY-MANAGEMENT) -->
    <div id="requests-timeline" class="space-y-4 pt-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Request Processing Timeline &bull; Who Requested</span>
                </h3>
                <p class="text-xs text-slate-500">Live timeline of department supply requisitions, requesting personnel, and approval statuses.</p>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('requests.index') }}" class="text-xs font-bold text-sky-600 hover:text-sky-800 hover:underline">
                    View Full Archive ({{ count($requestsTimeline) }}) &rarr;
                </a>
            </div>
        </div>

        <!-- Interactive Timeline Nodes -->
        <div class="space-y-4">
            @forelse(collect($requestsTimeline)->take(8) as $batch)
            <div class="bg-white rounded-2xl border {{ $batch['status'] === 'Pending' ? 'border-amber-300 ring-2 ring-amber-400/20 shadow-sm bg-amber-50/10' : 'border-slate-200 shadow-xs' }} p-5 sm:p-6 transition hover:shadow-md">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-2 flex-wrap">
                            <span class="font-mono font-bold text-xs text-sky-700 bg-sky-50 px-2 py-0.5 rounded-lg border border-sky-100">
                                {{ $batch['batch_id'] }}
                            </span>
                            <span class="text-base font-black text-slate-900">{{ $batch['department_name'] }}</span>
                            @if($batch['is_emergency'])
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                                    URGENT EMERGENCY
                                </span>
                            @endif
                        </div>

                        <!-- Who Requested -->
                        <div class="text-xs text-slate-700 flex items-center space-x-2 pt-0.5">
                            <span class="flex items-center space-x-1 font-bold text-sky-800">
                                <svg class="w-3.5 h-3.5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span>Requested by: <strong class="text-slate-900">{{ $batch['requested_by'] }}</strong></span>
                            </span>
                            <span>&bull;</span>
                            <span class="text-slate-500">{{ \Carbon\Carbon::parse($batch['created_at'])->format('F d, Y &bull; h:i A') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 self-start sm:self-auto">
                        @if($batch['status'] === 'Pending')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 flex items-center space-x-1">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                <span>PENDING APPROVAL</span>
                            </span>
                        @elseif($batch['status'] === 'Approved')
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                &check; ISSUED
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ strtoupper($batch['status']) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Purpose / Justification Box -->
                @if($batch['purpose'])
                <div class="mt-3 p-2.5 px-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 flex items-start space-x-2">
                    <span class="text-slate-400 font-bold uppercase text-[10px] flex-shrink-0 mt-0.5">Purpose:</span>
                    <span class="font-medium text-slate-800">{{ $batch['purpose'] }}</span>
                </div>
                @endif

                <!-- Requested Items List -->
                <div class="mt-4 space-y-2">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Requested Line Items:</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($batch['items'] as $it)
                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between text-xs">
                            <div class="min-w-0 pr-2">
                                <div class="font-bold text-slate-800 truncate">{{ $it->product?->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $it->product?->sku }} &bull; Available: {{ $it->product?->total_stock }} {{ $it->product?->unit_of_measure }}</div>
                            </div>
                            <div class="text-right flex-shrink-0 font-mono font-bold text-sky-700">
                                {{ $it->quantity_issued > 0 ? $it->quantity_issued : $it->quantity }} {{ $it->product?->unit_of_measure }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Actions & Issuance Signoff -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div>
                        @if($batch['issued_by'])
                            <span class="text-slate-500">
                                Issued by: <strong class="text-slate-800">{{ $batch['issued_by'] }}</strong>
                                @if($batch['issued_at'])
                                    on {{ \Carbon\Carbon::parse($batch['issued_at'])->format('M d, Y h:i A') }}
                                @endif
                            </span>
                        @else
                            <span class="text-amber-700 font-medium italic">Awaiting Supply Section officer review</span>
                        @endif
                    </div>

                    <div class="flex items-center space-x-2">
                        @if($batch['status'] === 'Pending')
                            <form method="POST" action="{{ route('requests.approve', $batch['batch_id']) }}" onsubmit="return confirm('Confirm approval and issuance of requested stock for {{ $batch['department_name'] }}?');">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                                    Approve &amp; Issue
                                </button>
                            </form>
                            <form method="POST" action="{{ route('requests.deny', $batch['batch_id']) }}" onsubmit="return confirm('Deny this requisition request?');">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-800 rounded-xl text-xs font-bold transition">
                                    Deny
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('requests.print', $batch['batch_id']) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print RIS Slip</span>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="font-bold text-slate-600">No Requisitions in the Timeline Yet</p>
                <p class="text-xs text-slate-400 mt-1">Submit a new request from the Requisition Portal to start tracking.</p>
                <a href="{{ route('requests.portal') }}" class="mt-4 inline-block px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold transition">
                    + Submit First Requisition
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Deliveries Tracking Section -->
    <div class="space-y-4 pt-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-900 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>Recent Inward Shipments &bull; Deliveries Log</span>
                </h3>
                <p class="text-xs text-slate-500">Latest supplier deliveries received and added to inventory batches.</p>
            </div>
            <a href="{{ route('deliveries.index') }}" class="text-xs font-bold text-teal-600 hover:text-teal-800 hover:underline">
                View All Deliveries &rarr;
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Delivery Ref</th>
                            <th class="py-3 px-4">Supplier / Consignor</th>
                            <th class="py-3 px-4">Delivery Date</th>
                            <th class="py-3 px-4">Received By</th>
                            <th class="py-3 px-4">Items Delivered</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentDeliveries as $del)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold text-sky-600">
                                <a href="{{ route('deliveries.show', $del) }}" class="hover:underline">
                                    {{ $del->delivery_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $del->supplier?->name }}
                            </td>
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $del->delivery_date->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 text-slate-600">
                                {{ $del->received_by }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 bg-slate-100 rounded-md font-bold text-slate-800 text-[11px]">
                                    {{ $del->items->count() }} items ({{ $del->items->sum('quantity') }} units)
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    RECEIVED
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('deliveries.show', $del) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-700 rounded-lg text-xs font-semibold transition">
                                    Voucher &rarr;
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Critical Stock Modal -->
    <div x-show="criticalModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-5 sm:p-6 shadow-xl space-y-4 max-h-[85vh] overflow-y-auto" @click.outside="criticalModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2">
                    <span class="p-2 bg-rose-100 text-rose-700 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <h3 class="text-base font-bold text-slate-900">Critical Stock Items ({{ $criticalSupplies->count() }})</h3>
                </div>
                <button type="button" @click="criticalModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <p class="text-xs text-slate-500">The following supplies have reached or fallen below their minimum reorder thresholds and require stock replenishment:</p>

            <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden max-h-96 overflow-y-auto custom-scroll">
                @foreach($criticalSupplies as $cs)
                <div class="p-3 flex items-center justify-between text-xs hover:bg-slate-50">
                    <div>
                        <div class="font-bold text-slate-900">{{ $cs->name }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">{{ $cs->sku }} &bull; {{ $cs->category?->name }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-rose-700 text-sm font-mono">{{ $cs->total_stock }} {{ $cs->unit_of_measure }}</div>
                        <div class="text-[10px] text-slate-400">Reorder Level: {{ $cs->min_stock_alert }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="button" @click="criticalModal = false" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold">
                    Close
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
