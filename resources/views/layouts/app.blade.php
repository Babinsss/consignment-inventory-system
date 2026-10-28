<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Medical Supplies & Pharmacy Inventory System') - RMPH HECS</title>
    
    <!-- Local Vite Assets (Fast, Offline-Ready) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tailwind CSS Runtime Fallback & Chart.js -->
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
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        .sidebar-scroll::-webkit-scrollbar,
        .custom-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-track,
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }

        @media print {
            aside, header, footer, .no-print, .mobile-bottom-nav { display: none !important; }
            .print-only { display: block !important; }
            body, html {
                height: auto !important;
                overflow: visible !important;
                background: #ffffff !important;
                color: #000000 !important;
            }
            main, .main-content-scroll {
                height: auto !important;
                overflow: visible !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
            }
            .shadow-xs, .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl { box-shadow: none !important; }
            .printable-sheet {
                box-shadow: none !important;
                border: 1px solid #94a3b8 !important;
                padding: 1rem !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-full bg-slate-100 text-slate-800 antialiased font-sans" 
      x-data="{ 
          sidebarOpen: false, 
          sidebarCollapsed: false,
          mobileActionOpen: false
      }">
    @php
        $currentUser = auth()->user() ?? \App\Models\User::where('role', \App\Models\User::ROLE_MEDICAL_SUPPLY)->first() ?? \App\Models\User::first();
        if (!auth()->check() && $currentUser) {
            auth()->login($currentUser);
        }
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row">

        <!-- 1. MOBILE BACKDROP & DRAWER (Off-canvas) -->
        <div x-show="sidebarOpen" 
             x-cloak 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs z-50 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        <div x-show="sidebarOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-250 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 w-72 bg-slate-900 z-50 lg:hidden flex flex-col shadow-2xl">
            @include('layouts.sidebar-content', ['isMobile' => true])
        </div>

        <!-- 2. DESKTOP PERMANENT VERTICAL SIDEBAR -->
        <aside class="hidden lg:flex lg:flex-col lg:flex-shrink-0 bg-slate-900 border-r border-slate-800 transition-all duration-300 ease-in-out no-print z-30 lg:h-screen lg:sticky lg:top-0"
               :class="sidebarCollapsed ? 'w-20' : 'w-72'">
            @include('layouts.sidebar-content', ['isMobile' => false])
        </aside>

        <!-- 3. MAIN CONTENT CONTAINER -->
        <div class="flex-1 flex flex-col min-w-0 bg-slate-50 min-h-screen">

            <!-- Sticky Top Header -->
            <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 flex-shrink-0 no-print shadow-xs">
                <div class="px-3 sm:px-6 h-16 flex items-center justify-between">
                    
                    <!-- Left: Hamburger & Breadcrumb Title -->
                    <div class="flex items-center space-x-2 sm:space-x-3 min-w-0">
                        <!-- Mobile Hamburger Button -->
                        <button type="button" 
                                @click="sidebarOpen = true" 
                                class="lg:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none transition"
                                title="Open Hospital Menu">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Desktop Sidebar Collapse Toggle -->
                        <button type="button" 
                                @click="sidebarCollapsed = !sidebarCollapsed" 
                                class="hidden lg:flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none" 
                                title="Toggle Sidebar">
                            <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': sidebarCollapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                            </svg>
                        </button>

                        <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

                        <!-- Hospital Header Identity -->
                        <div class="flex items-center space-x-2 truncate">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black bg-purple-100 text-purple-800 uppercase tracking-wider flex-shrink-0">
                                HECS
                            </span>
                            <div class="truncate">
                                <span class="text-xs sm:text-sm font-bold text-slate-900 block truncate leading-tight">
                                    ROXAS MEMORIAL PROVINCIAL HOSPITAL
                                </span>
                                <span class="text-[10px] text-slate-500 hidden sm:block truncate leading-tight">
                                    Capiz Provincial Government &bull; Consignment & Inventory Portal
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Quick Actions & Role Switcher -->
                    <div class="flex items-center space-x-2 sm:space-x-3 flex-shrink-0">
                        @if($currentUser && ($currentUser->isAdmin() || $currentUser->isMedicalSupplyStaff() || $currentUser->isPharmacyStaff()))
                            <!-- Desktop Quick Delivery -->
                            <a href="{{ route('deliveries.create') }}" 
                               class="hidden md:inline-flex items-center px-3 py-1.5 border border-sky-600 text-sky-700 hover:bg-sky-50 rounded-xl text-xs font-bold shadow-xs transition">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span>+ New Delivery</span>
                            </a>

                            <!-- Desktop Quick Dispense -->
                            <a href="{{ route('sales.create') }}" 
                               class="hidden sm:inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition shadow-emerald-600/20">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>+ Dispense Stock</span>
                            </a>
                        @endif

                        <!-- Stock Card Quick Link -->
                        @if($currentUser && ($currentUser->isAdmin() || $currentUser->isMedicalSupplyStaff()))
                            <a href="{{ route('hospital.stock-card') }}" 
                               class="hidden lg:inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 rounded-xl text-xs font-bold transition">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Stock Card</span>
                            </a>
                        @endif

                        <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                        <!-- Role Persona Switcher & Profile Dropdown -->
                        <div class="relative" x-data="{ roleOpen: false }" @click.outside="roleOpen = false">
                            <button @click="roleOpen = !roleOpen" 
                                    type="button" 
                                    class="flex items-center space-x-1.5 sm:space-x-2 p-1.5 rounded-xl hover:bg-slate-100 transition border border-slate-200 bg-slate-50 text-left">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center font-black text-xs text-white shadow-xs"
                                     style="background: {{ $currentUser?->isAdmin() ? '#e11d48' : ($currentUser?->isMedicalSupplyStaff() ? '#7e22ce' : ($currentUser?->isFinanceOfficer() ? '#059669' : '#0284c7')) }}">
                                    {{ strtoupper(substr($currentUser?->name ?? 'RX', 0, 2)) }}
                                </div>
                                <div class="hidden md:block text-left text-xs leading-tight pr-1">
                                    <p class="font-bold text-slate-900 truncate max-w-[130px]">{{ $currentUser?->name }}</p>
                                    <p class="text-[10px] text-purple-700 font-semibold">{{ $currentUser?->role_label }}</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Floating Role Dropdown -->
                            <div x-show="roleOpen" 
                                 x-cloak 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-50 divide-y divide-slate-100">
                                
                                <div class="px-4 py-2">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current Hospital Account</p>
                                    <p class="text-xs font-bold text-slate-900 mt-0.5 truncate">{{ $currentUser?->name }}</p>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $currentUser?->role_badge_color }}">
                                        {{ $currentUser?->role_label }}
                                    </span>
                                    <p class="text-[10px] text-slate-500 mt-1">{{ $currentUser?->department }}</p>
                                </div>

                                <div class="p-2 space-y-1">
                                    <p class="px-2 pt-1 text-[10px] font-bold uppercase tracking-wider text-purple-700">Quick Switch Persona:</p>
                                    
                                    <!-- Medical Supply Staff -->
                                    <form method="POST" action="{{ route('switch-role') }}">
                                        @csrf
                                        <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_MEDICAL_SUPPLY }}">
                                        <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-purple-50 hover:text-purple-900 flex items-center justify-between {{ $currentUser?->isMedicalSupplyStaff() ? 'bg-purple-50/80 font-bold text-purple-900' : 'text-slate-700' }}">
                                            <div>
                                                <span class="block text-xs font-bold">Consignment Staff</span>
                                                <span class="block text-[10px] text-slate-400">Aliana Marie A. Dula (CSR/HDU)</span>
                                            </div>
                                            @if($currentUser?->isMedicalSupplyStaff())<span class="text-xs text-purple-700 font-black">&check;</span>@endif
                                        </button>
                                    </form>

                                    <!-- Pharmacy Staff -->
                                    <form method="POST" action="{{ route('switch-role') }}">
                                        @csrf
                                        <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_PHARMACY }}">
                                        <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-sky-50 hover:text-sky-900 flex items-center justify-between {{ $currentUser?->isPharmacyStaff() ? 'bg-sky-50/80 font-bold text-sky-900' : 'text-slate-700' }}">
                                            <div>
                                                <span class="block text-xs font-bold">Pharmacy Staff</span>
                                                <span class="block text-[10px] text-slate-400">Angela Cruz, RPh (Pharmacy)</span>
                                            </div>
                                            @if($currentUser?->isPharmacyStaff())<span class="text-xs text-sky-700 font-black">&check;</span>@endif
                                        </button>
                                    </form>

                                    <!-- Finance Officer -->
                                    <form method="POST" action="{{ route('switch-role') }}">
                                        @csrf
                                        <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_FINANCE }}">
                                        <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-emerald-50 hover:text-emerald-900 flex items-center justify-between {{ $currentUser?->isFinanceOfficer() ? 'bg-emerald-50/80 font-bold text-emerald-900' : 'text-slate-700' }}">
                                            <div>
                                                <span class="block text-xs font-bold">Finance Auditor</span>
                                                <span class="block text-[10px] text-slate-400">Judy Lyn G. Bermoy (Auditor)</span>
                                            </div>
                                            @if($currentUser?->isFinanceOfficer())<span class="text-xs text-emerald-700 font-black">&check;</span>@endif
                                        </button>
                                    </form>

                                    <!-- Hospital Admin -->
                                    <form method="POST" action="{{ route('switch-role') }}">
                                        @csrf
                                        <input type="hidden" name="role" value="{{ \App\Models\User::ROLE_ADMIN }}">
                                        <button type="submit" class="w-full px-2.5 py-1.5 text-left rounded-lg text-xs font-medium hover:bg-rose-50 hover:text-rose-900 flex items-center justify-between {{ $currentUser?->isAdmin() ? 'bg-rose-50/80 font-bold text-rose-900' : 'text-slate-700' }}">
                                            <div>
                                                <span class="block text-xs font-bold">Hospital Admin</span>
                                                <span class="block text-[10px] text-slate-400">Dr. Florencio O. Luching, Jr.</span>
                                            </div>
                                            @if($currentUser?->isAdmin())<span class="text-xs text-rose-700 font-black">&check;</span>@endif
                                        </button>
                                    </form>
                                </div>

                                <div class="p-2 space-y-1">
                                    @if($currentUser?->isAdmin())
                                        <a href="{{ route('users.index') }}" class="block px-3 py-1.5 text-xs text-rose-700 hover:bg-rose-50 rounded-lg font-bold">
                                            &bull; Manage Staff Roles & Access
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 hover:text-slate-900 rounded-lg font-medium flex items-center space-x-1.5">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            <span>Sign Out</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Scrollable Body with Flash Messages, Main Content, and Footer -->
            <div class="flex-1 flex flex-col justify-between pb-20 lg:pb-0">
                <div>
                    <!-- Flash Messages -->
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full no-print">
                        @if(session('success'))
                            <div class="p-4 mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
                                <div class="flex items-center space-x-2.5">
                                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                                </div>
                                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="p-4 mb-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-xs">
                                <div class="flex items-center space-x-2.5">
                                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                                </div>
                                <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endif

                        @if(isset($errors) && $errors->any())
                            <div class="p-4 mb-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                                <div class="font-bold text-sm mb-1.5 flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Please correct the following errors:</span>
                                </div>
                                <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700 pl-2">
                                    @foreach($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Main Content Area -->
                    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-5 sm:py-6 w-full">
                        @yield('content')
                    </main>
                </div>

                <!-- Footer -->
                <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-6 lg:px-8 mt-auto no-print">
                    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 space-y-2 sm:space-y-0">
                        <div class="text-center sm:text-left">
                            <span class="font-bold text-slate-800">ROXAS MEMORIAL PROVINCIAL HOSPITAL</span> &bull; Hospital Extensive Consignment System (HECS)
                        </div>
                        <div class="text-[11px] text-slate-400 text-center sm:text-right">
                            Capiz Provincial Government &bull; General Form No. 18 / StCG / ScCG &bull; FEFO Management Active
                        </div>
                    </div>
                </footer>
            </div>

            <!-- 4. MOBILE BOTTOM APP BAR (For seamless mobile experience) -->
            <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200 z-40 px-3 py-2 flex items-center justify-around shadow-lg no-print">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex flex-col items-center justify-center text-[10px] font-semibold transition {{ request()->routeIs('dashboard') ? 'text-sky-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="mt-0.5">Home</span>
                </a>

                <!-- Deliveries -->
                <a href="{{ route('deliveries.index') }}" 
                   class="flex flex-col items-center justify-center text-[10px] font-semibold transition {{ request()->routeIs('deliveries.*') ? 'text-sky-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('deliveries.*') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="mt-0.5">Deliveries</span>
                </a>

                <!-- Central Floating Action (New Txn Modal / Action) -->
                <div class="relative -mt-6">
                    <button type="button" 
                            @click="mobileActionOpen = !mobileActionOpen"
                            class="w-12 h-12 rounded-full bg-gradient-to-tr from-sky-600 via-indigo-600 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-purple-600/40 border-4 border-slate-50 active:scale-95 transition">
                        <svg class="w-6 h-6 transition-transform duration-200" :class="{ 'rotate-45': mobileActionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>

                    <!-- Mobile Floating Action Popup Menu -->
                    <div x-show="mobileActionOpen" 
                         x-cloak 
                         @click.outside="mobileActionOpen = false"
                         x-transition:enter="transition ease-out duration-150 transform"
                         x-transition:enter-start="opacity-0 translate-y-4 scale-90"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100 transform"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 scale-90"
                         class="absolute bottom-16 left-1/2 -translate-x-1/2 w-56 bg-slate-900 text-white rounded-2xl shadow-2xl p-2 border border-slate-700 space-y-1 z-50">
                        <a href="{{ route('deliveries.create') }}" class="flex items-center space-x-2.5 p-2 rounded-xl hover:bg-slate-800 text-xs font-semibold">
                            <span class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </span>
                            <span>Record Delivery</span>
                        </a>
                        <a href="{{ route('sales.create') }}" class="flex items-center space-x-2.5 p-2 rounded-xl hover:bg-slate-800 text-xs font-semibold">
                            <span class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </span>
                            <span>Dispense Stock</span>
                        </a>
                        <a href="{{ route('hospital.stock-card') }}" class="flex items-center space-x-2.5 p-2 rounded-xl hover:bg-slate-800 text-xs font-semibold">
                            <span class="w-7 h-7 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span>Stock Card</span>
                        </a>
                    </div>
                </div>

                <!-- Dispensing / Sales -->
                <a href="{{ route('sales.index') }}" 
                   class="flex flex-col items-center justify-center text-[10px] font-semibold transition {{ request()->routeIs('sales.*') ? 'text-emerald-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('sales.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="mt-0.5">Dispense</span>
                </a>

                <!-- Products -->
                <a href="{{ route('products.index') }}" 
                   class="flex flex-col items-center justify-center text-[10px] font-semibold transition {{ request()->routeIs('products.*') ? 'text-sky-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('products.*') ? 'text-sky-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span class="mt-0.5">Inventory</span>
                </a>
            </nav>

        </div>

    </div>

    @stack('scripts')
</body>
</html>
