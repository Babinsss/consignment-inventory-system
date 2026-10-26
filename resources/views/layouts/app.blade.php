<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Medical Supplies & Pharmacy Inventory System') - RMPH HECS</title>
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
        
        /* Custom slim scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        @media print {
            aside, header, footer, .no-print { display: none !important; }
            .print-only { display: block !important; }
            body, html {
                height: auto !important;
                overflow: visible !important;
                background: #ffffff !important;
            }
            .sidebar-layout-container, .main-content-scroll, main {
                height: auto !important;
                overflow: visible !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl { box-shadow: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 antialiased overflow-hidden" 
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: false,
          topRoleOpen: false
      }">
    @php
        $currentUser = auth()->user() ?? \App\Models\User::where('role', \App\Models\User::ROLE_MEDICAL_SUPPLY)->first() ?? \App\Models\User::first();
        if (!auth()->check() && $currentUser) {
            auth()->login($currentUser);
        }
    @endphp

    <div class="h-full flex sidebar-layout-container">

        <!-- 1. MOBILE BACKDROP & DRAWER (Off-canvas) -->
        <div x-show="sidebarOpen" 
             x-cloak 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs z-50 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        <div x-show="sidebarOpen" 
             x-cloak
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 w-72 bg-slate-900 z-50 lg:hidden flex flex-col shadow-2xl">
            @include('layouts.sidebar-content', ['isMobile' => true])
        </div>

        <!-- 2. DESKTOP PERMANENT VERTICAL SIDEBAR -->
        <aside class="hidden lg:flex lg:flex-col lg:flex-shrink-0 bg-slate-900 border-r border-slate-800 transition-all duration-300 ease-in-out no-print z-30"
               :class="sidebarCollapsed ? 'w-20' : 'w-72'">
            @include('layouts.sidebar-content', ['isMobile' => false])
        </aside>

        <!-- 3. MAIN CONTENT CONTAINER (Header + Scrollable Main + Footer) -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50">

            <!-- Compact Top Bar -->
            <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 z-20 flex-shrink-0 no-print shadow-xs">
                <!-- Left: Sidebar Toggles & Breadcrumb -->
                <div class="flex items-center space-x-3">
                    <!-- Mobile Hamburger -->
                    <button type="button" 
                            @click="sidebarOpen = true" 
                            class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition"
                            title="Open Menu">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Desktop Collapse Toggle -->
                    <button type="button" 
                            @click="sidebarCollapsed = !sidebarCollapsed" 
                            class="hidden lg:flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition focus:outline-none" 
                            title="Toggle Sidebar Expand / Collapse">
                        <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                        </svg>
                    </button>

                    <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

                    <!-- Hospital Context Indicator -->
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-purple-100 text-purple-800 uppercase tracking-wider">
                            HECS
                        </span>
                        <div class="hidden sm:block">
                            <span class="text-xs font-bold text-slate-900 block leading-tight">ROXAS MEMORIAL PROVINCIAL HOSPITAL</span>
                            <span class="text-[10px] text-slate-500 block leading-tight">Capiz Provincial Government • Consignment & Inventory System</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Quick Actions & Role Switcher Badge -->
                <div class="flex items-center space-x-2 sm:space-x-3">
                    @if($currentUser && ($currentUser->isAdmin() || $currentUser->isMedicalSupplyStaff() || $currentUser->isPharmacyStaff()))
                        <!-- Quick Delivery Action -->
                        <a href="{{ route('deliveries.create') }}" 
                           class="hidden md:inline-flex items-center px-3 py-1.5 border border-sky-600 text-sky-700 hover:bg-sky-50 rounded-xl text-xs font-bold shadow-xs transition">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            + New Delivery
                        </a>

                        <!-- Quick Dispense Action -->
                        <a href="{{ route('sales.create') }}" 
                           class="hidden sm:inline-flex items-center px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-xs transition shadow-sky-600/20">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            + Issue / Dispense
                        </a>
                    @endif

                    <!-- Stock Card Quick Link -->
                    @if($currentUser && ($currentUser->isAdmin() || $currentUser->isMedicalSupplyStaff()))
                        <a href="{{ route('hospital.stock-card') }}" 
                           class="hidden lg:inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 rounded-xl text-xs font-bold transition">
                            🗃️ Stock Card
                        </a>
                    @endif

                    <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                    <!-- Top Bar Persona Menu -->
                    <div class="relative" x-data="{ roleOpen: false }" @click.outside="roleOpen = false">
                        <button @click="roleOpen = !roleOpen" 
                                type="button" 
                                class="flex items-center space-x-2 p-1.5 rounded-xl hover:bg-slate-100 transition border border-transparent hover:border-slate-200 text-left">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-xs text-white shadow-xs"
                                 style="background: {{ $currentUser?->isAdmin() ? '#e11d48' : ($currentUser?->isMedicalSupplyStaff() ? '#7e22ce' : ($currentUser?->isFinanceOfficer() ? '#059669' : '#0284c7')) }}">
                                {{ strtoupper(substr($currentUser?->name ?? 'RX', 0, 2)) }}
                            </div>
                            <div class="hidden sm:block text-left text-xs leading-tight pr-1">
                                <p class="font-bold text-slate-900 truncate max-w-[120px]">{{ $currentUser?->name }}</p>
                                <p class="text-[10px] text-purple-700 font-semibold">{{ $currentUser?->role_label }}</p>
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Persona & Role Switcher Menu -->
                        <div x-show="roleOpen" 
                             x-cloak 
                             class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-50 divide-y divide-slate-100">
                            <div class="px-4 py-2">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current Active Staff User</p>
                                <p class="text-xs font-bold text-slate-900 mt-0.5">{{ $currentUser?->name }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $currentUser?->role_badge_color }}">
                                    {{ $currentUser?->role_label }}
                                </span>
                                <p class="text-[10px] text-slate-500 mt-1">{{ $currentUser?->department }}</p>
                            </div>

                            <div class="p-2 space-y-1">
                                <p class="px-2 pt-1 text-[10px] font-bold uppercase tracking-wider text-purple-700">Switch Role Persona (Instant Demo):</p>
                                
                                <!-- Medical Supply Staff -->
                                <form method="POST" action="{{ route('switch-role') }}">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_MEDICAL_SUPPLY }}">
                                    <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-purple-50 hover:text-purple-900 flex items-center justify-between {{ $currentUser?->isMedicalSupplyStaff() ? 'bg-purple-50/70 font-bold text-purple-900' : 'text-slate-700' }}">
                                        <div>
                                            <span class="block">🩺 Consignment Staff</span>
                                            <span class="block text-[10px] text-slate-400">Aliana Marie A. Dula (CSR/HDU)</span>
                                        </div>
                                        @if($currentUser?->isMedicalSupplyStaff())<span class="text-xs text-purple-700">✓</span>@endif
                                    </button>
                                </form>

                                <!-- Pharmacy Staff -->
                                <form method="POST" action="{{ route('switch-role') }}">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_PHARMACY }}">
                                    <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-sky-50 hover:text-sky-900 flex items-center justify-between {{ $currentUser?->isPharmacyStaff() ? 'bg-sky-50/70 font-bold text-sky-900' : 'text-slate-700' }}">
                                        <div>
                                            <span class="block">💊 Pharmacy Staff</span>
                                            <span class="block text-[10px] text-slate-400">Angela Cruz, RPh (Pharmacy)</span>
                                        </div>
                                        @if($currentUser?->isPharmacyStaff())<span class="text-xs text-sky-700">✓</span>@endif
                                    </button>
                                </form>

                                <!-- Finance Officer -->
                                <form method="POST" action="{{ route('switch-role') }}">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_FINANCE }}">
                                    <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-emerald-50 hover:text-emerald-900 flex items-center justify-between {{ $currentUser?->isFinanceOfficer() ? 'bg-emerald-50/70 font-bold text-emerald-900' : 'text-slate-700' }}">
                                        <div>
                                            <span class="block">💼 Finance Officer</span>
                                            <span class="block text-[10px] text-slate-400">Judy Lyn G. Bermoy (Auditor)</span>
                                        </div>
                                        @if($currentUser?->isFinanceOfficer())<span class="text-xs text-emerald-700">✓</span>@endif
                                    </button>
                                </form>

                                <!-- Admin -->
                                <form method="POST" action="{{ route('switch-role') }}">
                                    @csrf
                                    <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_ADMIN }}">
                                    <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-rose-50 hover:text-rose-900 flex items-center justify-between {{ $currentUser?->isAdmin() ? 'bg-rose-50/70 font-bold text-rose-900' : 'text-slate-700' }}">
                                        <div>
                                            <span class="block">👑 Hospital Admin</span>
                                            <span class="block text-[10px] text-slate-400">Dr. Florencio O. Luching, Jr.</span>
                                        </div>
                                        @if($currentUser?->isAdmin())<span class="text-xs text-rose-700">✓</span>@endif
                                    </button>
                                </form>
                            </div>

                            <div class="p-2 space-y-1">
                                @if($currentUser?->isAdmin())
                                    <a href="{{ route('users.index') }}" class="block px-3 py-1.5 text-xs text-rose-700 hover:bg-rose-50 rounded-lg font-bold">
                                        ⚙️ Manage Staff Roles & Access
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 hover:text-slate-900 rounded-lg font-medium">
                                        🚪 Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Body with Flash Messages, Main Content, and Footer -->
            <div class="flex-1 overflow-y-auto main-content-scroll flex flex-col justify-between">
                <div>
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
                    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
                        @yield('content')
                    </main>
                </div>

                <!-- Footer -->
                <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-6 lg:px-8 mt-auto no-print">
                    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 space-y-2 sm:space-y-0">
                        <div>
                            <span class="font-bold text-slate-700">ROXAS MEMORIAL PROVINCIAL HOSPITAL</span> • Hospital Extensive Consignment System (HECS)
                        </div>
                        <div class="text-[11px] text-slate-400">
                            Capiz Provincial Government • General Form No. 18 / StCG / ScCG • FEFO Management Active
                        </div>
                    </div>
                </footer>
            </div>
        </div>

    </div>

    @stack('scripts')
</body>
</html>
