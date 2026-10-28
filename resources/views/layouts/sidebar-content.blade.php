@php
    $currentUser = auth()->user() ?? \App\Models\User::where('role', \App\Models\User::ROLE_MEDICAL_SUPPLY)->first() ?? \App\Models\User::first();
    $isMobile = $isMobile ?? false;
@endphp

<div class="h-full flex flex-col justify-between text-slate-300 select-none">
    <!-- Top Branding & Navigation -->
    <div class="flex-1 flex flex-col min-h-0">
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-800 bg-slate-950/60 flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-500 via-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-purple-600/30 flex-shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div class="leading-tight truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span class="text-xs font-black tracking-wider text-white block">ROXAS MEMORIAL</span>
                    <span class="text-[10px] font-bold text-sky-400 tracking-wider block">PROVINCIAL HOSPITAL</span>
                    <span class="text-[9px] text-purple-300 font-semibold tracking-tight block">HECS &bull; Consignment System</span>
                </div>
            </a>

            @if($isMobile)
                <button type="button" @click="sidebarOpen = false" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Scrollable Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5 sidebar-scroll">
            
            <!-- GROUP: CORE -->
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-slate-400"
                   :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Core Operations</span>
                    <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">&bull;</span>
                </p>
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" 
                       title="Dashboard"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-sky-500/15 text-sky-400 border border-sky-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-sky-400' : 'text-slate-400 group-hover:text-white' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: HECS CONSIGNMENT FORMS -->
            @if($currentUser?->canAccessConsignment() || $currentUser?->isAdmin() || $currentUser?->isFinanceOfficer())
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-purple-400"
                   :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">HECS Hospital Forms</span>
                    <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">HECS</span>
                </p>
                <div class="space-y-1">
                    <!-- Stock Card (General Form 18) -->
                    <a href="{{ route('hospital.stock-card') }}" 
                       title="Stock Card (Gen Form 18)"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('hospital.stock-card') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('hospital.stock-card') ? 'text-purple-400' : 'text-slate-400 group-hover:text-purple-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="flex-1 truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                            <span class="block truncate">Stock Card (Form 18)</span>
                            <span class="block text-[10px] text-purple-400/80">General Form No. 18</span>
                        </div>
                    </a>

                    <!-- STCG -->
                    <a href="{{ route('hospital.stcg') }}" 
                       title="STCG - Statement of Consignment Goods"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('hospital.stcg') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('hospital.stcg') ? 'text-purple-400' : 'text-slate-400 group-hover:text-purple-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="flex-1 truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                            <span class="block truncate">STCG Monthly Report</span>
                            <span class="block text-[10px] text-purple-400/80">Statement of Consigned Goods</span>
                        </div>
                    </a>

                    <!-- ScCG -->
                    <a href="{{ route('hospital.sccg') }}" 
                       title="ScCG - Summary of Consignment Goods"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('hospital.sccg') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('hospital.sccg') ? 'text-purple-400' : 'text-slate-400 group-hover:text-purple-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <div class="flex-1 truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                            <span class="block truncate">ScCG Supplier Summary</span>
                            <span class="block text-[10px] text-purple-400/80">Consignor Reconciliation</span>
                        </div>
                    </a>

                    <!-- Monthly Physical Inventory -->
                    <a href="{{ route('hospital.monthly-inventory') }}" 
                       title="Monthly Physical Inventory Sheet"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('hospital.monthly-inventory') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('hospital.monthly-inventory') ? 'text-purple-400' : 'text-slate-400 group-hover:text-purple-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div class="flex-1 truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                            <span class="block truncate">Monthly Inventory Sheet</span>
                            <span class="block text-[10px] text-purple-400/80">Physical Count & Valuation</span>
                        </div>
                    </a>

                    <!-- Bulk Import -->
                    <a href="{{ route('hospital.import') }}" 
                       title="Bulk Excel/CSV Import"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('hospital.import') ? 'bg-purple-500/20 text-purple-300 border border-purple-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('hospital.import') ? 'text-purple-400' : 'text-slate-400 group-hover:text-purple-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <div class="flex-1 truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                            <span class="block truncate">Excel/CSV Bulk Import</span>
                            <span class="block text-[10px] text-purple-400/80">Migration & Templates</span>
                        </div>
                    </a>
                </div>
            </div>
            @endif

            <!-- GROUP: OPERATIONS & INVENTORY -->
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-sky-400"
                   :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Inventory & Stock</span>
                    <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">OPS</span>
                </p>
                <div class="space-y-1">
                    <!-- Deliveries -->
                    <a href="{{ route('deliveries.index') }}" 
                       title="Deliveries & Inward Shipments"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('deliveries.*') ? 'bg-sky-500/20 text-sky-300 border border-sky-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('deliveries.*') ? 'text-sky-400' : 'text-slate-400 group-hover:text-sky-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Deliveries & Inward</span>
                    </a>

                    <!-- Products / Items -->
                    <a href="{{ route('products.index') }}" 
                       title="Products & Medical Supplies"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('products.*') ? 'bg-sky-500/20 text-sky-300 border border-sky-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('products.*') ? 'text-sky-400' : 'text-slate-400 group-hover:text-sky-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Products & Catalog</span>
                    </a>

                    <!-- Dispensing / Sales -->
                    <a href="{{ route('sales.index') }}" 
                       title="Sales & Dispensing"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('sales.*') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('sales.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Dispensing & Issues</span>
                    </a>

                    <!-- Suppliers / Consignors -->
                    <a href="{{ route('suppliers.index') }}" 
                       title="Suppliers & Consignors"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('suppliers.*') ? 'bg-sky-500/20 text-sky-300 border border-sky-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('suppliers.*') ? 'text-sky-400' : 'text-slate-400 group-hover:text-sky-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Suppliers & Consignors</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: FINANCIALS & AUDIT REPORTS -->
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-emerald-400"
                   :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Audit & Financials</span>
                    <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">FIN</span>
                </p>
                <div class="space-y-1">
                    @if($currentUser?->isFinanceOfficer() || $currentUser?->isAdmin())
                    <!-- Financial Ledger -->
                    <a href="{{ route('financial.index') }}" 
                       title="Financial Ledger & Margins"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('financial.*') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('financial.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Financial Settlements</span>
                    </a>
                    @endif

                    <!-- Expiry Watch -->
                    <a href="{{ route('reports.expiry') }}" 
                       title="Expiry Watch & Batches"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('reports.expiry') ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('reports.expiry') ? 'text-amber-400' : 'text-slate-400 group-hover:text-amber-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Expiry Watch (FEFO)</span>
                    </a>

                    <!-- Low Stock Alerts -->
                    <a href="{{ route('reports.low-stock') }}" 
                       title="Low Stock & Reorder Points"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('reports.low-stock') ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('reports.low-stock') ? 'text-rose-400' : 'text-slate-400 group-hover:text-rose-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Low Stock Alerts</span>
                    </a>

                    <!-- Stock Valuation -->
                    <a href="{{ route('reports.valuation') }}" 
                       title="Valuation Report"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('reports.valuation') ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('reports.valuation') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Inventory Valuation</span>
                    </a>

                    <!-- Audit Log -->
                    <a href="{{ route('reports.audit-log') }}" 
                       title="Stock Movement Audit Log"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('reports.audit-log') ? 'bg-sky-500/20 text-sky-300 border border-sky-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('reports.audit-log') ? 'text-sky-400' : 'text-slate-400 group-hover:text-sky-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Audit Movement Trail</span>
                    </a>
                </div>
            </div>

            <!-- GROUP: ADMIN (Only for Admin) -->
            @if($currentUser?->isAdmin())
            <div>
                <p class="px-2.5 mb-1.5 text-[10px] font-extrabold uppercase tracking-widest text-rose-400"
                   :class="{ 'text-center text-[9px]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <span :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Administration</span>
                    <span :class="{ 'hidden': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }} }">ADM</span>
                </p>
                <div class="space-y-1">
                    <a href="{{ route('users.index') }}" 
                       title="Staff & Roles Management"
                       class="group flex items-center px-3 py-2 text-xs font-medium rounded-xl transition {{ request()->routeIs('users.*') ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('users.*') ? 'text-rose-400' : 'text-slate-400 group-hover:text-rose-300' }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="truncate" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">Hospital Staff Accounts</span>
                    </a>
                </div>
            </div>
            @endif

        </nav>
    </div>

    <!-- Bottom User Profile Card & Persona Switcher -->
    <div class="p-3 border-t border-slate-800 bg-slate-950/70 flex-shrink-0">
        <div class="rounded-xl bg-slate-900/90 border border-slate-800 p-2.5"
             :class="{ 'p-1.5': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs text-white flex-shrink-0 shadow-xs"
                     style="background: {{ $currentUser?->isAdmin() ? '#e11d48' : ($currentUser?->isMedicalSupplyStaff() ? '#7e22ce' : ($currentUser?->isFinanceOfficer() ? '#059669' : '#0284c7')) }}">
                    {{ strtoupper(substr($currentUser?->name ?? 'RX', 0, 2)) }}
                </div>
                <div class="truncate flex-1" :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                    <p class="text-xs font-bold text-white truncate">{{ $currentUser?->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ $currentUser?->role_label }}</p>
                </div>
            </div>

            <!-- Quick Persona Switch Buttons (Visible on Mobile & Expanded Desktop) -->
            <div class="mt-2.5 pt-2 border-t border-slate-800 space-y-1"
                 :class="{ 'hidden': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} }">
                <div class="text-[9px] font-bold uppercase tracking-wider text-slate-400 px-1 mb-1">
                    Quick Role Switch:
                </div>
                <div class="grid grid-cols-2 gap-1 text-[10px]">
                    <!-- Consignment Staff -->
                    <form method="POST" action="{{ route('switch-role') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_MEDICAL_SUPPLY }}">
                        <button type="submit" class="w-full text-left px-2 py-1 rounded bg-slate-800/80 hover:bg-purple-900/50 hover:text-purple-200 transition truncate {{ $currentUser?->isMedicalSupplyStaff() ? 'text-purple-300 font-bold border border-purple-500/40' : 'text-slate-400' }}" title="Switch to Consignment Staff">
                            CSR / HECS
                        </button>
                    </form>
                    <!-- Pharmacy Staff -->
                    <form method="POST" action="{{ route('switch-role') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_PHARMACY }}">
                        <button type="submit" class="w-full text-left px-2 py-1 rounded bg-slate-800/80 hover:bg-sky-900/50 hover:text-sky-200 transition truncate {{ $currentUser?->isPharmacyStaff() ? 'text-sky-300 font-bold border border-sky-500/40' : 'text-slate-400' }}" title="Switch to Pharmacy Staff">
                            Pharmacy
                        </button>
                    </form>
                    <!-- Finance Officer -->
                    <form method="POST" action="{{ route('switch-role') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_FINANCE }}">
                        <button type="submit" class="w-full text-left px-2 py-1 rounded bg-slate-800/80 hover:bg-emerald-900/50 hover:text-emerald-200 transition truncate {{ $currentUser?->isFinanceOfficer() ? 'text-emerald-300 font-bold border border-emerald-500/40' : 'text-slate-400' }}" title="Switch to Finance Auditor">
                            Finance
                        </button>
                    </form>
                    <!-- Hospital Admin -->
                    <form method="POST" action="{{ route('switch-role') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_ADMIN }}">
                        <button type="submit" class="w-full text-left px-2 py-1 rounded bg-slate-800/80 hover:bg-rose-900/50 hover:text-rose-200 transition truncate {{ $currentUser?->isAdmin() ? 'text-rose-300 font-bold border border-rose-500/40' : 'text-slate-400' }}" title="Switch to Hospital Admin">
                            Admin
                        </button>
                    </form>
                </div>

                <div class="mt-2 pt-1 flex items-center justify-between text-xs">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="w-full text-center px-2 py-1 text-[11px] text-slate-400 hover:text-rose-300 hover:bg-rose-950/40 rounded transition flex items-center justify-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
