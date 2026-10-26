<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Medical Supplies & Pharmacy Inventory System')</title>
    <!-- Tailwind CSS CDN for guaranteed instant styles & compiled Vite bundle -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#0284c7',
                            600: '#0369a1',
                            700: '#075985',
                            800: '#0c4a6e',
                            900: '#082f49',
                        },
                        emerald: {
                            600: '#059669',
                            700: '#047857'
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: #ffffff !important; }
            .shadow-sm, .shadow-md, .shadow-lg { box-shadow: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col">

        <!-- Top Navigation Bar -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40 no-print shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <button type="button" @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-500 hover:text-slate-700 p-2 rounded-lg">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 to-teal-500 flex items-center justify-center text-white shadow-md shadow-sky-500/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-lg font-bold tracking-tight text-slate-900 block leading-tight">ROXAS MEMORIAL PROVINCIAL HOSPITAL</span>
                                <span class="text-xs text-sky-600 font-semibold tracking-wide">Hospital Extensive Consignment System (HECS) • Capiz Provincial Gov.</span>
                            </div>
                        </a>
                    </div>

                    <!-- Quick Action Buttons & User -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <a href="{{ route('deliveries.create') }}" class="inline-flex items-center px-3.5 py-1.5 border border-sky-600 text-sky-600 hover:bg-sky-50 rounded-lg text-xs font-semibold shadow-xs transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            + New Delivery
                        </a>

                        <a href="{{ route('sales.create') }}" class="inline-flex items-center px-3.5 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-xs transition shadow-sky-600/20">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            + Dispense / Issue
                        </a>

                        <div class="h-6 w-px bg-slate-200"></div>

                        <!-- User Profile Badge -->
                        <div class="flex items-center space-x-2 pl-1">
                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs ring-2 ring-white">
                                RX
                            </div>
                            <div class="hidden sm:block text-left text-xs leading-tight">
                                <p class="font-semibold text-slate-800">Hospital Pharmacy</p>
                                <p class="text-slate-400">Inventory & Finance</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Primary Navigation Tabs -->
            <div class="bg-slate-50 border-t border-slate-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <nav class="flex space-x-1 sm:space-x-4 overflow-x-auto py-2 text-xs font-medium" aria-label="Tabs">
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg flex items-center space-x-1.5 {{ request()->routeIs('dashboard') ? 'bg-white text-sky-700 font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('deliveries.index') }}" class="px-3 py-2 rounded-lg flex items-center space-x-1.5 {{ request()->routeIs('deliveries.*') ? 'bg-white text-sky-700 font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                            <span>Deliveries & Receiving</span>
                        </a>

                        <a href="{{ route('products.index') }}" class="px-3 py-2 rounded-lg flex items-center space-x-1.5 {{ request()->routeIs('products.*') ? 'bg-white text-sky-700 font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            <span>Medical & Pharmacy Stocks</span>
                        </a>

                        <a href="{{ route('sales.index') }}" class="px-3 py-2 rounded-lg flex items-center space-x-1.5 {{ request()->routeIs('sales.*') ? 'bg-white text-sky-700 font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span>Dispensing / POS</span>
                        </a>

                        <a href="{{ route('financial.index') }}" class="px-3 py-2 rounded-lg flex items-center space-x-1.5 {{ request()->routeIs('financial.*') ? 'bg-white text-sky-700 font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Financial & Consignment</span>
                        </a>

                        <a href="{{ route('suppliers.index') }}" class="px-3 py-2 rounded-lg flex items-center space-x-1.5 {{ request()->routeIs('suppliers.*') ? 'bg-white text-sky-700 font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span>Consignors & Suppliers</span>
                        </a>

                        <!-- HECS Hospital Consignment Documents Dropdown -->
                        <div class="relative" x-data="{ hecsOpen: false }" @click.outside="hecsOpen = false">
                            <button @click="hecsOpen = !hecsOpen" class="px-3 py-2 rounded-lg flex items-center space-x-1.5 {{ request()->routeIs('hospital.*') ? 'bg-white text-purple-700 font-bold shadow-xs border border-slate-200 ring-1 ring-purple-300' : 'text-purple-700 font-semibold hover:bg-purple-50' }}">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>HECS Hospital Forms</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-purple-100 text-purple-800 font-black">RMPH</span>
                                <svg class="w-3.5 h-3.5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="hecsOpen" x-cloak class="absolute left-0 mt-1 w-64 bg-white rounded-xl shadow-xl border border-slate-200 py-1.5 z-50 divide-y divide-slate-100">
                                <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Capiz Provincial Gov. Templates</div>
                                <div>
                                    <a href="{{ route('hospital.stcg') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-medium">
                                        📋 Statement of Consumed Goods (StCG)
                                    </a>
                                    <a href="{{ route('hospital.sccg') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-medium">
                                        📑 Schedule of Consumed Goods (ScCG)
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('hospital.stock-card') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-medium">
                                        🗃️ Stock Card (General Form No. 18)
                                    </a>
                                    <a href="{{ route('hospital.monthly-inventory') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-purple-50 hover:text-purple-700 font-medium">
                                        📊 Monthly Consignment Inventory Report
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('hospital.import') }}" class="block px-4 py-2 text-xs text-purple-700 bg-purple-50 hover:bg-purple-100 font-semibold flex items-center justify-between">
                                        <span>📥 Excel Data Importer Hub</span>
                                        <span class="px-1.5 py-0.5 text-[9px] bg-purple-600 text-white rounded font-bold">XLSX</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open" class="px-3 py-2 rounded-lg flex items-center space-x-1.5 {{ request()->routeIs('reports.*') ? 'bg-white text-sky-700 font-semibold shadow-xs border border-slate-200' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>Audit & Reports</span>
                                <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-cloak class="absolute left-0 mt-1 w-52 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50">
                                <a href="{{ route('reports.expiry') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-700">⏳ FEFO Expiry Audit</a>
                                <a href="{{ route('reports.valuation') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-700">💎 Stock Valuation Breakdown</a>
                                <a href="{{ route('reports.low-stock') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-700">⚠️ Low Stock Reorder List</a>
                                <a href="{{ route('reports.audit-log') }}" class="block px-4 py-2 text-xs text-slate-700 hover:bg-sky-50 hover:text-sky-700">📜 Stock Movement Audit Trail</a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full no-print">
            @if(session('success'))
                <div class="p-4 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                    <div class="font-medium text-sm mb-1">Please correct the following errors:</div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 mt-auto no-print">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 space-y-2 sm:space-y-0">
                <div>
                    <span class="font-semibold text-slate-700">Medical Supplies & Pharmacy Consignment Inventory System</span> • Hospital Grade Tracking
                </div>
                <div>
                    FEFO Batch Management & Financial Ledger Active
                </div>
            </div>
        </footer>

    </div>

    @stack('scripts')
</body>
</html>
