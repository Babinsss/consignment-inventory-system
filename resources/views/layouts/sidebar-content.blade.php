@php
    $currentUser = auth()->user() ?? \App\Models\User::where('role', \App\Models\User::ROLE_MEDICAL_SUPPLY)->first() ?? \App\Models\User::first();
    $isMobile = $isMobile ?? false;
@endphp

<div class="h-full flex flex-col justify-between text-slate-300">
    <!-- Top Branding & Navigation Links -->
    <div class="flex-1 flex flex-col min-h-0">
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 bg-slate-950/40 flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 via-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-purple-600/30 flex-shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="leading-tight truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span class="text-xs font-black tracking-wider text-white block">ROXAS MEMORIAL</span>
                    <span class="text-[10px] font-bold text-sky-400 tracking-wider block">PROVINCIAL HOSPITAL</span>
                    <span class="text-[9px] text-purple-300 font-semibold tracking-tight block">HECS • Capiz Provincial Gov.</span>
                </div>
            </a>

            @if($isMobile)
                <button type="button" @click="sidebarOpen = false" class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Scrollable Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5 sidebar-scroll">
            <!-- GROUP: OVERVIEW -->
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-slate-400"
                   :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Core System</span>
                    <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">•</span>
                </p>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       title="Dashboard"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-sky-500/15 text-sky-400 border border-sky-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 mr-3 {{ request()->routeIs('dashboard') ? 'text-sky-400' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: CONSIGNMENT OPERATIONS (HECS) -->
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-purple-400"
                   :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">HECS Consignment</span>
                    <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">HECS</span>
                </p>
                <div class="space-y-1">
                    @if($currentUser?->canAccessConsignment() || $currentUser?->isAdmin())
                        <!-- HECS Hospital Forms (Collapsible Submenu) -->
                        <div x-data="{ subOpen: {{ request()->routeIs('hospital.*') ? 'true' : 'false' }} }">
                            <button type="button" 
                                    @click="subOpen = !subOpen"
                                    title="HECS Hospital Forms"
                                    class="w-full group flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition {{ request()->routeIs('hospital.*') ? 'bg-purple-950/60 text-purple-300 border border-purple-500/40' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                                <div class="flex items-center min-w-0">
                                    <svg class="w-5 h-5 flex-shrink-0 mr-3 text-purple-400 group-hover:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">HECS Hospital Forms</span>
                                </div>
                                <div class="flex items-center space-x-1.5" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-purple-600/60 text-purple-200 font-black">RMPH</span>
                                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': subOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>

                            <!-- Submenu Items -->
                            <div x-show="subOpen" x-cloak class="mt-1 pl-4 space-y-0.5 border-l border-slate-800 ml-5"
                                 :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                                <a href="{{ route('hospital.stock-card') }}" 
                                   class="block px-2.5 py-1.5 text-[11px] rounded-lg transition {{ request()->routeIs('hospital.stock-card') ? 'text-purple-300 bg-purple-900/40 font-bold border-l-2 border-purple-400' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                                    🗃️ Stock Card (GF-18)
                                </a>
                                <a href="{{ route('hospital.stcg') }}" 
                                   class="block px-2.5 py-1.5 text-[11px] rounded-lg transition {{ request()->routeIs('hospital.stcg') ? 'text-purple-300 bg-purple-900/40 font-bold border-l-2 border-purple-400' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                                    📋 StCG Report
                                </a>
                                <a href="{{ route('hospital.sccg') }}" 
                                   class="block px-2.5 py-1.5 text-[11px] rounded-lg transition {{ request()->routeIs('hospital.sccg') ? 'text-purple-300 bg-purple-900/40 font-bold border-l-2 border-purple-400' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                                    📑 ScCG Schedule
                                </a>
                                <a href="{{ route('hospital.monthly-inventory') }}" 
                                   class="block px-2.5 py-1.5 text-[11px] rounded-lg transition {{ request()->routeIs('hospital.monthly-inventory') ? 'text-purple-300 bg-purple-900/40 font-bold border-l-2 border-purple-400' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                                    📊 Monthly Inventory
                                </a>
                                <a href="{{ route('hospital.import') }}" 
                                   class="block px-2.5 py-1.5 text-[11px] rounded-lg transition flex items-center justify-between {{ request()->routeIs('hospital.import') ? 'text-purple-200 bg-purple-900/60 font-bold' : 'text-purple-400 hover:text-purple-200 hover:bg-slate-800/60' }}">
                                    <span>📥 Excel Importer</span>
                                    <span class="px-1.5 py-0.5 text-[8px] bg-purple-600 text-white rounded font-bold">XLSX</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Inward Deliveries / Receiving -->
                    @if($currentUser?->isMedicalSupplyStaff() || $currentUser?->isPharmacyStaff() || $currentUser?->isAdmin())
                        <a href="{{ route('deliveries.index') }}" 
                           title="Deliveries & Receiving"
                           class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('deliveries.*') ? 'bg-sky-500/15 text-sky-400 border border-sky-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 mr-3 {{ request()->routeIs('deliveries.*') ? 'text-sky-400' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/>
                            </svg>
                            <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Deliveries & Receiving</span>
                        </a>
                    @endif

                    <!-- Inventory: Consignment Supplies / Pharmacy Stocks -->
                    <a href="{{ route('products.index', $currentUser?->isMedicalSupplyStaff() ? ['inventory_type' => 'consignment'] : ($currentUser?->isPharmacyStaff() ? ['item_type' => 'pharmacy'] : [])) }}" 
                       title="{{ $currentUser?->isMedicalSupplyStaff() ? 'Consignment Supplies' : ($currentUser?->isPharmacyStaff() ? 'Pharmacy Stocks' : 'Medical & Pharmacy Stocks') }}"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('products.*') ? 'bg-sky-500/15 text-sky-400 border border-sky-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 mr-3 {{ request()->routeIs('products.*') ? 'text-sky-400' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                            {{ $currentUser?->isMedicalSupplyStaff() ? 'Consignment Supplies' : ($currentUser?->isPharmacyStaff() ? 'Pharmacy Stocks' : 'Medical & Pharmacy Stocks') }}
                        </span>
                    </a>

                    <!-- CSR / Ward Issuances / Sales -->
                    @if($currentUser?->isMedicalSupplyStaff() || $currentUser?->isPharmacyStaff() || $currentUser?->isAdmin())
                        <a href="{{ route('sales.index') }}" 
                           title="{{ $currentUser?->isMedicalSupplyStaff() ? 'CSR / Ward Issuance' : 'Dispensing / POS' }}"
                           class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('sales.*') ? 'bg-sky-500/15 text-sky-400 border border-sky-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 mr-3 {{ request()->routeIs('sales.*') ? 'text-sky-400' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                                {{ $currentUser?->isMedicalSupplyStaff() ? 'CSR / Ward Issuance' : 'Dispensing / POS' }}
                            </span>
                        </a>
                    @endif

                    <!-- Consignors & Suppliers -->
                    <a href="{{ route('suppliers.index') }}" 
                       title="Consignors & Suppliers"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('suppliers.*') ? 'bg-sky-500/15 text-sky-400 border border-sky-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 mr-3 {{ request()->routeIs('suppliers.*') ? 'text-sky-400' : 'text-slate-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Consignors & Suppliers</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: FINANCIAL MANAGEMENT -->
            @if($currentUser?->canAccessFinancial() || $currentUser?->isAdmin())
                <div>
                    <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-emerald-400"
                       :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                        <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Accounting & Audit</span>
                        <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">$$</span>
                    </p>
                    <div class="space-y-1">
                        <a href="{{ route('financial.index') }}" 
                           title="Financial & Consignment Settlements"
                           class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('financial.*') ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0 mr-3 {{ request()->routeIs('financial.*') ? 'text-emerald-400' : 'text-emerald-500 group-hover:text-emerald-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Financial Ledger & Settlement</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- GROUP: AUDIT & REPORTS -->
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-slate-400"
                   :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Reports & Audits</span>
                    <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">REP</span>
                </p>
                <div class="space-y-1" x-data="{ auditOpen: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }">
                    <button type="button" 
                            @click="auditOpen = !auditOpen"
                            title="Audit & Reports"
                            class="w-full group flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition {{ request()->routeIs('reports.*') ? 'bg-sky-950/60 text-sky-300 border border-sky-500/40' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <div class="flex items-center min-w-0">
                            <svg class="w-5 h-5 flex-shrink-0 mr-3 text-sky-400 group-hover:text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Audit & Inventory Logs</span>
                        </div>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': auditOpen, 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Submenu Items -->
                    <div x-show="auditOpen" x-cloak class="mt-1 pl-4 space-y-0.5 border-l border-slate-800 ml-5"
                         :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                        <a href="{{ route('reports.expiry') }}" 
                           class="block px-2.5 py-1.5 text-[11px] rounded-lg transition {{ request()->routeIs('reports.expiry') ? 'text-sky-300 bg-sky-900/40 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            ⏳ FEFO Expiry Audit
                        </a>
                        <a href="{{ route('reports.valuation') }}" 
                           class="block px-2.5 py-1.5 text-[11px] rounded-lg transition {{ request()->routeIs('reports.valuation') ? 'text-sky-300 bg-sky-900/40 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            💎 Stock Valuation
                        </a>
                        <a href="{{ route('reports.low-stock') }}" 
                           class="block px-2.5 py-1.5 text-[11px] rounded-lg transition {{ request()->routeIs('reports.low-stock') ? 'text-sky-300 bg-sky-900/40 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            ⚠️ Low Stock Reorders
                        </a>
                        <a href="{{ route('reports.audit-log') }}" 
                           class="block px-2.5 py-1.5 text-[11px] rounded-lg transition {{ request()->routeIs('reports.audit-log') ? 'text-sky-300 bg-sky-900/40 font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                            📜 Movement Audit Trail
                        </a>
                    </div>
                </div>
            </div>

            <!-- GROUP: ADMINISTRATION (ADMIN ONLY) -->
            @if($currentUser?->isAdmin())
                <div>
                    <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-rose-400"
                       :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                        <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Administration</span>
                        <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">ADM</span>
                    </p>
                    <div class="space-y-1">
                        <a href="{{ route('users.index') }}" 
                           title="Staff Roles & Access"
                           class="group flex items-center justify-between px-3 py-2 text-xs font-semibold rounded-xl transition {{ request()->routeIs('users.*') ? 'bg-rose-500/15 text-rose-300 border border-rose-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <div class="flex items-center min-w-0">
                                <svg class="w-5 h-5 flex-shrink-0 mr-3 text-rose-400 group-hover:text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Staff Roles & Access</span>
                            </div>
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-rose-500 text-white font-black"
                                  :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">ADMIN</span>
                        </a>
                    </div>
                </div>
            @endif
        </nav>
    </div>

    <!-- Active User & Persona Switcher Footer -->
    <div class="p-3 border-t border-slate-800 bg-slate-950/60 relative" x-data="{ switcherOpen: false }">
        <!-- User Badge Button -->
        <button type="button" 
                @click="switcherOpen = !switcherOpen" 
                class="w-full flex items-center space-x-3 p-2 rounded-xl hover:bg-slate-800/80 transition text-left group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs text-white shadow-md flex-shrink-0"
                 style="background: {{ $currentUser?->isAdmin() ? '#e11d48' : ($currentUser?->isMedicalSupplyStaff() ? '#7e22ce' : ($currentUser?->isFinanceOfficer() ? '#059669' : '#0284c7')) }}">
                {{ strtoupper(substr($currentUser?->name ?? 'RX', 0, 2)) }}
            </div>
            <div class="min-w-0 flex-1 truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                <p class="text-xs font-bold text-white truncate">{{ $currentUser?->name }}</p>
                <div class="flex items-center space-x-1.5 mt-0.5">
                    <span class="inline-block px-1.5 py-0.2 rounded-full text-[9px] font-bold border {{ $currentUser?->role_badge_color }}">
                        {{ $currentUser?->role_label }}
                    </span>
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-400 group-hover:text-white transition flex-shrink-0" 
                 :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
            </svg>
        </button>

        <!-- Role Switcher Popover -->
        <div x-show="switcherOpen" 
             x-cloak 
             @click.outside="switcherOpen = false"
             class="absolute bottom-16 left-3 right-3 sm:w-72 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-2.5 z-50 divide-y divide-slate-800">
            <div class="pb-2 px-1">
                <p class="text-[9px] font-bold uppercase tracking-wider text-purple-400">Current Active Identity</p>
                <p class="text-xs font-bold text-white mt-0.5">{{ $currentUser?->name }}</p>
                <p class="text-[10px] text-slate-400">{{ $currentUser?->department }} ({{ $currentUser?->title }})</p>
            </div>

            <div class="py-2 space-y-1">
                <p class="px-1 text-[9px] font-bold uppercase tracking-wider text-slate-400">Quick Switch Persona:</p>
                
                <!-- Consignment Staff -->
                <form method="POST" action="{{ route('switch-role') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_MEDICAL_SUPPLY }}">
                    <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-purple-900/50 hover:text-white flex items-center justify-between {{ $currentUser?->isMedicalSupplyStaff() ? 'bg-purple-900/60 font-bold text-purple-200' : 'text-slate-300' }}">
                        <div>
                            <span class="block">🩺 Consignment Staff</span>
                            <span class="block text-[9px] text-slate-400">Aliana Marie A. Dula (CSR/HDU)</span>
                        </div>
                        @if($currentUser?->isMedicalSupplyStaff())<span class="text-xs text-purple-400">✓</span>@endif
                    </button>
                </form>

                <!-- Pharmacy Staff -->
                <form method="POST" action="{{ route('switch-role') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_PHARMACY }}">
                    <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-sky-900/50 hover:text-white flex items-center justify-between {{ $currentUser?->isPharmacyStaff() ? 'bg-sky-900/60 font-bold text-sky-200' : 'text-slate-300' }}">
                        <div>
                            <span class="block">💊 Pharmacy Staff</span>
                            <span class="block text-[9px] text-slate-400">Angela Cruz, RPh (Pharmacy)</span>
                        </div>
                        @if($currentUser?->isPharmacyStaff())<span class="text-xs text-sky-400">✓</span>@endif
                    </button>
                </form>

                <!-- Finance Officer -->
                <form method="POST" action="{{ route('switch-role') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_FINANCE }}">
                    <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-emerald-900/50 hover:text-white flex items-center justify-between {{ $currentUser?->isFinanceOfficer() ? 'bg-emerald-900/60 font-bold text-emerald-200' : 'text-slate-300' }}">
                        <div>
                            <span class="block">💼 Finance Officer</span>
                            <span class="block text-[9px] text-slate-400">Judy Lyn G. Bermoy (Auditor)</span>
                        </div>
                        @if($currentUser?->isFinanceOfficer())<span class="text-xs text-emerald-400">✓</span>@endif
                    </button>
                </form>

                <!-- Admin -->
                <form method="POST" action="{{ route('switch-role') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_ADMIN }}">
                    <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-rose-900/50 hover:text-white flex items-center justify-between {{ $currentUser?->isAdmin() ? 'bg-rose-900/60 font-bold text-rose-200' : 'text-slate-300' }}">
                        <div>
                            <span class="block">👑 Hospital Admin</span>
                            <span class="block text-[9px] text-slate-400">Dr. Florencio O. Luching, Jr.</span>
                        </div>
                        @if($currentUser?->isAdmin())<span class="text-xs text-rose-400">✓</span>@endif
                    </button>
                </form>
            </div>

            <div class="pt-2 space-y-1">
                @if($currentUser?->isAdmin())
                    <a href="{{ route('users.index') }}" class="block px-2.5 py-1.5 text-xs text-rose-300 hover:bg-rose-900/40 rounded-lg font-bold">
                        ⚙️ Manage Staff Accounts
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-2.5 py-1.5 text-xs text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg font-medium">
                        🚪 Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
