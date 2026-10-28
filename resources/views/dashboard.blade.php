@extends('layouts.app')

@section('title', 'Hospital Inventory & Financial Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Alert Notification Bar if there are critical expiries or low stock items -->
    @if($criticalExpiryCount > 0 || $lowStockProducts->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($criticalExpiryCount > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between shadow-xs gap-3">
            <div class="flex items-center space-x-3">
                <span class="p-2 bg-amber-100 rounded-xl text-amber-700 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
                <div>
                    <h4 class="text-sm font-bold text-amber-900">Critical Expiry Warning</h4>
                    <p class="text-xs text-amber-700"><strong>{{ $criticalExpiryCount }}</strong> active batch(es) will expire within the next 30 days.</p>
                </div>
            </div>
            <a href="{{ route('reports.expiry', ['days' => 30]) }}" class="text-xs font-semibold px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl transition flex-shrink-0 self-end sm:self-auto">View Batches</a>
        </div>
        @endif

        @if($lowStockProducts->count() > 0)
        <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between shadow-xs gap-3">
            <div class="flex items-center space-x-3">
                <span class="p-2 bg-rose-100 rounded-xl text-rose-700 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
                <div>
                    <h4 class="text-sm font-bold text-rose-900">Low Stock Alert</h4>
                    <p class="text-xs text-rose-700"><strong>{{ $lowStockProducts->count() }}</strong> item(s) have reached or fallen below minimum threshold.</p>
                </div>
            </div>
            <a href="{{ route('reports.low-stock') }}" class="text-xs font-semibold px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl transition flex-shrink-0 self-end sm:self-auto">Reorder List</a>
        </div>
        @endif
    </div>
    @endif

    <!-- Page Header & Live Status -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Medical Supplies & Pharmacy Operations</h1>
            <p class="text-xs sm:text-sm text-slate-500">Real-time stock valuation, inward delivery tracking, and financial margin ledger.</p>
        </div>
        <div class="flex items-center space-x-2 self-start sm:self-auto flex-wrap gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full animate-pulse"></span> FEFO & Stock Ledger Active
            </span>
            <a href="{{ route('financial.index') }}" class="px-3.5 py-1.5 bg-slate-900 text-white hover:bg-slate-800 rounded-xl text-xs font-semibold shadow-xs transition">
                Financial Reports &rarr;
            </a>
        </div>
    </div>

    <!-- Quick Operations Launchpad (Mobile & Desktop Friendly) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <a href="{{ route('deliveries.create') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-sky-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-sky-600">Receive Stock</span>
            <span class="text-[10px] text-slate-400">Inward Delivery</span>
        </a>

        <a href="{{ route('sales.create') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-emerald-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-emerald-600">Dispense Stock</span>
            <span class="text-[10px] text-slate-400">Prescription / Issue</span>
        </a>

        <a href="{{ route('hospital.stock-card') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-purple-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-purple-600">Stock Card</span>
            <span class="text-[10px] text-slate-400">General Form 18</span>
        </a>

        <a href="{{ route('hospital.stcg') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-indigo-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-indigo-600">STCG Report</span>
            <span class="text-[10px] text-slate-400">Consignment Goods</span>
        </a>

        <a href="{{ route('hospital.monthly-inventory') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-amber-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-amber-600">Physical Count</span>
            <span class="text-[10px] text-slate-400">Monthly Sheet</span>
        </a>

        <a href="{{ route('hospital.import') }}" class="bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-teal-400 hover:shadow-md transition group text-center flex flex-col items-center justify-center">
            <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            </span>
            <span class="text-xs font-bold text-slate-800 mt-2 block group-hover:text-teal-600">Bulk Import</span>
            <span class="text-[10px] text-slate-400">Excel / CSV Data</span>
        </a>
    </div>

    <!-- 4 Main Financial KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Card 1: Total Inventory Asset Valuation -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs hover:border-sky-300 transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Stock Value</span>
                <span class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">&#8369;{{ number_format($totalInventoryValue, 2) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Pharmacy: <strong class="text-slate-800">&#8369;{{ number_format($totalPharmacyValue, 0) }}</strong></span>
                <span>Supplies: <strong class="text-slate-800">&#8369;{{ number_format($totalMedicalSupplyValue, 0) }}</strong></span>
            </div>
        </div>

        <!-- Card 2: Deliveries Expenditure -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs hover:border-teal-300 transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Deliveries Billed</span>
                <span class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">&#8369;{{ number_format($totalDeliveriesValue, 2) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">This Month: <strong class="text-teal-700">&#8369;{{ number_format($deliveriesThisMonth, 2) }}</strong></span>
                <a href="{{ route('deliveries.index') }}" class="text-teal-600 hover:underline font-semibold">View &rarr;</a>
            </div>
        </div>

        <!-- Card 3: Total Dispensing Revenue -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs hover:border-emerald-300 transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dispensing Revenue</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">&#8369;{{ number_format($totalSalesRevenue, 2) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>COGS: <strong class="text-slate-800">&#8369;{{ number_format($totalCostOfGoodsSold, 2) }}</strong></span>
                <span class="text-emerald-600 font-semibold">Net Rev</span>
            </div>
        </div>

        <!-- Card 4: Gross Profit & Profit Margin -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs hover:border-indigo-300 transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gross Profit Margin</span>
                <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                    {{ $overallMargin }}%
                </span>
            </div>
            <div class="text-2xl font-extrabold text-indigo-700">&#8369;{{ number_format($totalGrossProfit, 2) }}</div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Avg Margin: <strong class="text-indigo-600 font-bold">{{ $overallMargin }}%</strong></span>
                <span class="text-slate-500">Consignment: <strong>&#8369;{{ number_format($totalConsignmentValue, 0) }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Secondary KPI Strip: Inventory & Payable Statistics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <a href="{{ route('products.index', ['item_type' => 'pharmacy']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-sky-400 shadow-xs transition group">
            <div class="text-xs text-slate-500 group-hover:text-sky-600 font-medium flex items-center space-x-1">
                <span>Pharmacy Stocks</span>
            </div>
            <div class="text-lg sm:text-xl font-bold text-slate-800 mt-1">{{ $totalPharmacyProducts }} <span class="text-xs font-normal text-slate-400">Products</span></div>
        </a>
        <a href="{{ route('products.index', ['item_type' => 'medical_supply']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-sky-400 shadow-xs transition group">
            <div class="text-xs text-slate-500 group-hover:text-sky-600 font-medium flex items-center space-x-1">
                <span>Medical Supplies</span>
            </div>
            <div class="text-lg sm:text-xl font-bold text-slate-800 mt-1">{{ $totalMedicalSupplies }} <span class="text-xs font-normal text-slate-400">Products</span></div>
        </a>
        <a href="{{ route('products.index', ['inventory_type' => 'consignment']) }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-sky-400 shadow-xs transition group">
            <div class="text-xs text-slate-500 group-hover:text-sky-600 font-medium flex items-center space-x-1">
                <span>Consignment Items</span>
            </div>
            <div class="text-lg sm:text-xl font-bold text-slate-800 mt-1">{{ $totalConsignmentProducts }} <span class="text-xs font-normal text-slate-400">Items</span></div>
        </a>
        <a href="{{ route('financial.index') }}" class="bg-white p-4 rounded-2xl border border-slate-200 hover:border-rose-400 shadow-xs transition group">
            <div class="text-xs text-slate-500 group-hover:text-rose-600 font-medium flex items-center space-x-1">
                <span>Unpaid Payables</span>
            </div>
            <div class="text-lg sm:text-xl font-bold text-rose-600 mt-1">&#8369;{{ number_format($pendingPayables, 2) }}</div>
        </a>
    </div>

    <!-- HECS Consignment Operations Banner -->
    <div class="bg-gradient-to-r from-purple-900 via-indigo-900 to-slate-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black tracking-widest bg-purple-500/30 text-purple-200 border border-purple-400/30 uppercase">
                    HECS &bull; Hospital Extensive Consignment System
                </span>
                <h2 class="text-xl sm:text-2xl font-black mt-2 tracking-tight">Capiz Provincial Government Consignment Reconciliation</h2>
                <p class="text-xs text-purple-200/90 max-w-2xl mt-1">
                    Central Supply Room (CSR) and Hemodialysis Unit (HDU) consignment inventory reconciliation under General Form No. 18 / StCG guidelines.
                </p>
            </div>
            <div class="flex items-center space-x-2 self-start md:self-auto flex-wrap gap-2">
                <a href="{{ route('hospital.stock-card') }}" class="px-4 py-2 bg-white text-purple-900 hover:bg-purple-50 rounded-xl text-xs font-bold shadow-md transition">
                    Open Stock Card &rarr;
                </a>
                <a href="{{ route('hospital.stcg') }}" class="px-4 py-2 bg-purple-600/40 hover:bg-purple-600/60 border border-purple-400/30 text-white rounded-xl text-xs font-semibold transition">
                    View STCG Report
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-white/10 relative z-10">
            <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                <div class="text-[11px] font-bold text-purple-300 uppercase tracking-wide">Owed to Consignors</div>
                <div class="text-lg sm:text-xl font-black text-white mt-1">&#8369;{{ number_format($hecsConsignorCollectibles, 2) }}</div>
                <div class="text-[10px] text-purple-300/80 mt-0.5">Payable for Consumed Stocks</div>
            </div>

            <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                <div class="text-[11px] font-bold text-emerald-300 uppercase tracking-wide">Hospital Markup Retained</div>
                <div class="text-lg sm:text-xl font-black text-emerald-400 mt-1">&#8369;{{ number_format($hecsHospitalProfit, 2) }}</div>
                <div class="text-[10px] text-emerald-300/80 mt-0.5">Net RMPH Consignment Revenue</div>
            </div>

            <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                <div class="text-[11px] font-bold text-sky-300 uppercase tracking-wide">Gross Consignment Dispensed</div>
                <div class="text-lg sm:text-xl font-black text-sky-300 mt-1">&#8369;{{ number_format($hecsGrossSales, 2) }}</div>
                <div class="text-[10px] text-sky-300/80 mt-0.5">Total Patient & Ward Billing</div>
            </div>

            <div class="bg-white/5 rounded-2xl p-3.5 border border-white/10">
                <div class="text-[11px] font-bold text-indigo-300 uppercase tracking-wide">Active Consignment Catalog</div>
                <div class="text-lg sm:text-xl font-black text-white mt-1">{{ number_format($totalConsignmentProducts) }} <span class="text-xs font-normal text-purple-200">SKUs</span></div>
                <div class="text-[10px] text-indigo-300/80 mt-0.5">Across HDU & CSR Departments</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Line/Bar Chart: Monthly Financial Performance -->
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Financial Performance & Deliveries Inflow</h3>
                    <p class="text-xs text-slate-500">Deliveries Cost vs Sales Revenue vs Gross Profit over last 6 months</p>
                </div>
                <div class="flex items-center space-x-2 text-xs flex-wrap gap-1">
                    <span class="inline-flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-teal-500 mr-1.5"></span> Deliveries</span>
                    <span class="inline-flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-sky-500 mr-1.5"></span> Revenue</span>
                    <span class="inline-flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 mr-1.5"></span> Profit</span>
                </div>
            </div>
            <div class="h-64 sm:h-72">
                <canvas id="financialChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Stock Value Distribution -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-900">Inventory Distribution</h3>
                <p class="text-xs text-slate-500">Asset share by category & consignment status</p>
            </div>
            <div class="h-48 sm:h-52 my-auto flex items-center justify-center py-2">
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="pt-3 border-t border-slate-100 grid grid-cols-3 text-center text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px]">Pharmacy</span>
                    <strong class="text-slate-800">&#8369;{{ number_format($totalPharmacyValue, 0) }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">Medical</span>
                    <strong class="text-slate-800">&#8369;{{ number_format($totalMedicalSupplyValue, 0) }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">Consignment</span>
                    <strong class="text-slate-800">&#8369;{{ number_format($totalConsignmentValue, 0) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- 2 Column Data Tables: Recent Deliveries vs Recent Dispensations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Deliveries (Inward Tracking) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Recent Inward Deliveries</span>
                    </h3>
                    <p class="text-xs text-slate-500">Latest stocks received into inventory</p>
                </div>
                <a href="{{ route('deliveries.create') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">+ New Delivery</a>
            </div>
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-2.5 px-4">Delivery #</th>
                            <th class="py-2.5 px-4">Supplier</th>
                            <th class="py-2.5 px-4">Amount</th>
                            <th class="py-2.5 px-4">Payment</th>
                            <th class="py-2.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentDeliveries as $del)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-medium text-slate-900">
                                <a href="{{ route('deliveries.show', $del) }}" class="text-sky-600 hover:underline font-bold">
                                    {{ $del->delivery_number }}
                                </a>
                                <span class="block text-[10px] text-slate-400">{{ $del->delivery_date->format('M d, Y') }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                {{ Str::limit($del->supplier->name, 22) }}
                                <span class="block text-[10px] text-slate-400">{{ $del->items->count() }} items received</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                &#8369;{{ number_format($del->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                @if($del->payment_status === 'paid')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Paid</span>
                                @elseif($del->payment_status === 'partially_paid')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Partially Paid</span>
                                @elseif($del->payment_status === 'consignment_term')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-800">Consignment</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">Unpaid</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('deliveries.show', $del) }}" class="text-slate-500 hover:text-sky-600 font-medium">View &rarr;</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">No deliveries recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-right">
                <a href="{{ route('deliveries.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">View All Deliveries ({{ \App\Models\Delivery::count() }}) &rarr;</a>
            </div>
        </div>

        <!-- Recent Dispensations (Outward / POS) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Recent Dispensations & Billing</span>
                    </h3>
                    <p class="text-xs text-slate-500">Issued to patients, departments, and wards</p>
                </div>
                <a href="{{ route('sales.create') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">+ Dispense</a>
            </div>
            <div class="overflow-x-auto custom-scroll">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-2.5 px-4">Txn #</th>
                            <th class="py-2.5 px-4">Recipient</th>
                            <th class="py-2.5 px-4">Revenue</th>
                            <th class="py-2.5 px-4">Profit</th>
                            <th class="py-2.5 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentDispensations as $sale)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-medium text-slate-900">
                                <a href="{{ route('sales.show', $sale) }}" class="text-sky-600 hover:underline font-bold">
                                    {{ $sale->sale_number }}
                                </a>
                                <span class="block text-[10px] text-slate-400">{{ $sale->sale_date->format('M d, H:i') }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                {{ $sale->patient_name ?: $sale->department_name ?: 'Walk-in' }}
                                <span class="block text-[10px] text-slate-400">{{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                &#8369;{{ number_format($sale->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-emerald-700 font-semibold">&#8369;{{ number_format($sale->total_profit, 2) }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $sale->margin_percentage }}% margin</span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('sales.show', $sale) }}" class="text-slate-500 hover:text-sky-600 font-medium">Receipt &rarr;</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">No dispensations recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-right">
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">View All Transactions ({{ \App\Models\Sale::count() }}) &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Expiry Watchlist (FEFO Critical Watch) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                    <span>Expiration Watchlist (FEFO Earliest Batches)</span>
                </h3>
                <p class="text-xs text-slate-500">Batches needing immediate dispensing or rotation before expiration</p>
            </div>
            <a href="{{ route('reports.expiry') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">Full Expiry Audit Report &rarr;</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            @forelse($expiringBatches as $batch)
            <div class="p-3.5 rounded-2xl border {{ $batch->expiry_status === 'expired' ? 'bg-red-50/50 border-red-200' : ($batch->expiry_status === 'critical' ? 'bg-amber-50/50 border-amber-200' : 'bg-slate-50 border-slate-200') }}">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $batch->product->item_type === 'pharmacy' ? 'text-sky-700' : 'text-teal-700' }}">
                            {{ $batch->product->item_type === 'pharmacy' ? 'Pharmacy' : 'Medical Supply' }}
                        </span>
                        <h4 class="text-xs font-bold text-slate-900 line-clamp-1">{{ $batch->product->name }}</h4>
                    </div>
                    @if($batch->days_until_expiry < 0)
                        <span class="px-2 py-0.5 bg-red-100 text-red-800 font-bold text-[10px] rounded-full">EXPIRED</span>
                    @elseif($batch->days_until_expiry <= 30)
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded-full">{{ $batch->days_until_expiry }}d left</span>
                    @else
                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 font-bold text-[10px] rounded-full">{{ $batch->days_until_expiry }}d left</span>
                    @endif
                </div>
                <div class="mt-2 text-xs flex items-center justify-between text-slate-600">
                    <span>Batch: <strong class="text-slate-800 font-mono">{{ $batch->batch_number }}</strong></span>
                    <span>Rem: <strong class="text-slate-800">{{ $batch->quantity_remaining }}</strong> {{ $batch->product->unit_of_measure }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-500 flex items-center justify-between">
                    <span>Expiry: {{ $batch->expiry_date->format('M d, Y') }}</span>
                    <span>Val: &#8369;{{ number_format($batch->quantity_remaining * $batch->unit_purchase_cost, 2) }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-3 py-6 text-center text-slate-400 text-xs">All active stock batches have safe expiry dates (&gt;90 days).</div>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Financial Performance Chart
        const finCanvas = document.getElementById('financialChart');
        if (finCanvas) {
            const finCtx = finCanvas.getContext('2d');
            new Chart(finCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartMonths) !!},
                    datasets: [
                        {
                            label: 'Deliveries Invoiced (₱)',
                            data: {!! json_encode($deliveriesChartData) !!},
                            backgroundColor: 'rgba(20, 184, 166, 0.7)',
                            borderColor: '#0d9488',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Dispensing Revenue (₱)',
                            data: {!! json_encode($salesChartData) !!},
                            backgroundColor: 'rgba(2, 132, 199, 0.7)',
                            borderColor: '#0284c7',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Gross Profit (₱)',
                            data: {!! json_encode($profitChartData) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderColor: '#059669',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + (value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value);
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Inventory Category Distribution Chart
        const catCanvas = document.getElementById('categoryChart');
        if (catCanvas) {
            const catCtx = catCanvas.getContext('2d');
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categoryLabels ?? ['Pharmacy', 'Medical Supplies', 'Consignment']) !!},
                    datasets: [{
                        data: {!! json_encode($categoryValues ?? [0, 0, 0]) !!},
                        backgroundColor: [
                            '#0284c7', '#059669', '#7e22ce', '#f59e0b', '#06b6d4', 
                            '#6366f1', '#ec4899', '#84cc16', '#64748b'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, font: { size: 10 } }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endpush
