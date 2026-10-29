<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Supply Requisition Portal - Roxas Memorial Provincial Hospital</title>
    
    <!-- Local Vite Assets & Tailwind CSS Fallback -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
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

    <style>
        [x-cloak] { display: none !important; }
        .custom-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased bg-slate-100" x-data="publicRequisitionPortal()">

    <!-- Toast Notification Popup -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-5 right-5 z-50 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center space-x-3 text-xs border border-slate-700" 
         x-cloak>
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span x-text="toast.message" class="font-medium"></span>
    </div>

    <!-- Standalone Public Portal Top Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Branding -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 via-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-md shadow-sky-600/30 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-black tracking-wider text-slate-900 block leading-tight">ROXAS MEMORIAL PROVINCIAL HOSPITAL</span>
                    <span class="text-[11px] font-bold text-sky-600 tracking-wide block">Public Department Requisition Portal</span>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center space-x-3">
                <!-- Cart Preview Pill -->
                <button type="button" @click="activeTab = 'cart'" class="inline-flex items-center space-x-2 px-3 py-1.5 bg-sky-50 text-sky-700 hover:bg-sky-100 rounded-xl text-xs font-bold transition border border-sky-200/80">
                    <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Cart: <strong x-text="cartTotalUnits"></strong> units</span>
                </button>

                <!-- Internal Staff Login Link -->
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1 shadow-xs">
                        <span>Staff Dashboard</span>
                        <span>&rarr;</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1 shadow-xs">
                        <span>Staff Login</span>
                        <span>&rarr;</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- Informational Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-indigo-950 text-white rounded-3xl p-6 sm:p-7 shadow-sm border border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center space-x-2 text-xs font-bold text-sky-400 uppercase tracking-widest">
                    <span>Central Supply Room &bull; Supply Section</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Department Supply Requisition Portal</h1>
                <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                    Public requisition desk strictly for authorized hospital departments: <strong>CSR</strong>, <strong>DIALYSIS</strong>, <strong>PHARMACY OPD</strong>, <strong>PHARMACY MAIN</strong>, &amp; <strong>GUGMA DIALYSIS</strong>. Browse active inventory and submit Requisition and Issue Slips (RIS).
                </p>
            </div>

            <div class="flex items-center space-x-2 flex-wrap gap-2 self-start md:self-auto">
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-white/10 text-sky-200 border border-white/10">
                    <span class="w-2 h-2 mr-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                    Live Stock Catalog
                </span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-sky-500/20 text-sky-300 border border-sky-400/30">
                    5 Departments Authorized
                </span>
            </div>
        </div>

        <!-- Success Notification with Print Button -->
        @if(session('submitted_batch_id'))
        <div class="bg-emerald-50 border-2 border-emerald-400 p-5 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm animate-pulse">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xl flex-shrink-0 shadow-xs">
                    &check;
                </div>
                <div>
                    <h4 class="text-sm font-bold text-emerald-950">Requisition Batch Submitted Successfully!</h4>
                    <p class="text-xs text-emerald-800">Control Slip Number: <strong class="font-mono text-emerald-900 text-sm">{{ session('submitted_batch_id') }}</strong>. Automatically queued for Supply Section issuance.</p>
                </div>
            </div>
            <a href="{{ route('requests.print', session('submitted_batch_id')) }}" target="_blank" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center space-x-1.5 self-end sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Requisition Slip (RIS)</span>
            </a>
        </div>
        @endif

        <!-- Mobile Tab Switcher -->
        <div class="flex lg:hidden bg-slate-200 p-1 rounded-2xl text-xs font-bold">
            <button type="button" @click="activeTab = 'items'" :class="activeTab === 'items' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600'" class="flex-1 py-2.5 rounded-xl transition flex items-center justify-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>1. View Available Items</span>
            </button>
            <button type="button" @click="activeTab = 'cart'" :class="activeTab === 'cart' ? 'bg-white text-sky-700 shadow-xs' : 'text-slate-600'" class="flex-1 py-2.5 rounded-xl transition flex items-center justify-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>2. Requisition Cart (<span x-text="cartTotalUnits"></span>)</span>
            </button>
        </div>

        <!-- TWO-COLUMN SEPARATED LAYOUT -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- ============================================== -->
            <!-- LEFT PANE: REQUISITION DETAILS & CART CHECKOUT -->
            <!-- ============================================== -->
            <div class="lg:col-span-5 space-y-6" :class="{ 'hidden lg:block': activeTab === 'items' }">
                <form method="POST" action="{{ route('requests.submit') }}" @submit="onSubmitForm($event)" class="space-y-6">
                    @csrf
                    <input type="hidden" name="cart_data" :value="JSON.stringify(cart)">

                    <!-- Requisition Header Form Card -->
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs">1</span>
                                <h2 class="text-xs font-black text-slate-800 uppercase tracking-wider">Requesting Department</h2>
                            </div>
                            <label class="flex items-center space-x-2 cursor-pointer bg-amber-50 px-2.5 py-1 rounded-xl border border-amber-200">
                                <input type="checkbox" name="is_emergency" value="1" x-model="isEmergency" class="rounded text-amber-600 focus:ring-amber-500 h-3.5 w-3.5">
                                <span class="text-[11px] font-bold text-amber-900 select-none">Urgent Emergency</span>
                            </label>
                        </div>

                        <!-- Emergency Warning Alert -->
                        <div x-show="isEmergency" x-cloak class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 flex items-center space-x-2">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Emergency requests are prioritized for immediate clinical release.</span>
                        </div>

                        <!-- Department Selection (ONLY THE 5 ALLOWED DEPARTMENTS) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Select Department *</label>
                            <select name="department_name" x-model="selectedDepartment" @change="onDepartmentChange()" required class="w-full text-xs font-bold text-slate-900 rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-slate-50">
                                <option value="">-- Choose Authorized Department --</option>
                                <option value="CSR">CSR (Central Supply Room)</option>
                                <option value="DIALYSIS">DIALYSIS (Hemodialysis Unit)</option>
                                <option value="PHARMACY OPD">PHARMACY OPD</option>
                                <option value="PHARMACY MAIN">PHARMACY MAIN</option>
                                <option value="GUGMA DIALYSIS">GUGMA DIALYSIS (Dialysis Center)</option>
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Authorized for CSR, Dialysis, Pharmacy OPD, Pharmacy Main, &amp; Gugma Dialysis.</p>
                        </div>

                        <!-- Requested By (Auto-fills Head, allows personnel name) -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Requested By (Personnel / Head Name) *</label>
                            <input type="text" name="requested_by" x-model="requestedBy" required placeholder="Personnel Name" class="w-full text-xs font-semibold text-slate-800 rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
                                <span>Official Department Head: <strong class="text-slate-700" x-text="currentDepartmentHead || 'None selected'"></strong></span>
                                <button type="button" @click="requestedBy = currentDepartmentHead" x-show="currentDepartmentHead" class="text-sky-600 font-bold hover:underline">Use Head</button>
                            </div>
                        </div>

                        <!-- Purpose / Justification -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Clinical Purpose / Remarks *</label>
                            <textarea name="purpose" x-model="purpose" rows="2" required placeholder="State clinical reason (e.g. Ward dialysis run, ICU emergency, sterile pack preparation)..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Cart Summary & Items Card -->
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs">2</span>
                                <h2 class="text-xs font-black text-slate-800 uppercase tracking-wider">Requisition Cart Items</h2>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-sky-100 text-sky-800" x-text="cart.length + ' item(s)'"></span>
                        </div>

                        <!-- Empty Cart State -->
                        <div x-show="cart.length === 0" class="py-10 text-center text-slate-400 space-y-2">
                            <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="text-xs font-semibold text-slate-600">Your Requisition Cart is Empty</p>
                            <p class="text-[11px] text-slate-400">Select items from the "Available Items" directory on the right to add them here.</p>
                            <button type="button" @click="activeTab = 'items'" class="lg:hidden mt-2 px-3.5 py-1.5 bg-sky-50 text-sky-700 text-xs font-bold rounded-xl border border-sky-200">
                                Browse Available Items &rarr;
                            </button>
                        </div>

                        <!-- Cart Items List -->
                        <div x-show="cart.length > 0" class="space-y-3">
                            <div class="max-h-80 overflow-y-auto custom-scroll space-y-2 pr-1">
                                <template x-for="(item, idx) in cart" :key="item.id">
                                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between gap-3 text-xs hover:border-slate-300 transition">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-slate-900 truncate" x-text="item.name"></div>
                                            <div class="text-[10px] text-slate-400 font-mono flex items-center space-x-1.5 mt-0.5">
                                                <span x-text="item.sku"></span>
                                                <span>&bull;</span>
                                                <span class="text-slate-600 font-semibold" x-text="'Max: ' + item.stock + ' ' + item.unit"></span>
                                            </div>
                                        </div>

                                        <!-- Quantity Stepper -->
                                        <div class="flex items-center space-x-1 flex-shrink-0">
                                            <button type="button" @click="decrementQty(item)" class="w-6 h-6 rounded-lg bg-white border border-slate-300 text-slate-700 font-bold flex items-center justify-center hover:bg-slate-100">
                                                -
                                            </button>
                                            <input type="number" min="1" :max="item.stock" x-model.number="item.qty" class="w-14 text-center font-mono font-bold text-xs rounded-lg border border-slate-300 py-1 px-1 focus:ring-1 focus:ring-sky-500">
                                            <button type="button" @click="incrementQty(item)" class="w-6 h-6 rounded-lg bg-white border border-slate-300 text-slate-700 font-bold flex items-center justify-center hover:bg-slate-100">
                                                +
                                            </button>
                                        </div>

                                        <!-- Remove Line -->
                                        <button type="button" @click="removeItem(idx)" class="text-rose-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-50 transition" title="Remove item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Totals & Actions -->
                            <div class="pt-3 border-t border-slate-100 space-y-1 text-xs">
                                <div class="flex justify-between text-slate-500">
                                    <span>Line Items in Cart:</span>
                                    <strong class="text-slate-800" x-text="cart.length"></strong>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span>Total Requested Units:</span>
                                    <strong class="text-sky-700 font-bold" x-text="cartTotalUnits + ' units'"></strong>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center space-x-2">
                                <button type="button" @click="cart = []" class="px-3 py-2.5 text-xs text-rose-600 hover:bg-rose-50 rounded-xl font-bold transition">
                                    Clear Cart
                                </button>
                                <button type="submit" :disabled="cart.length === 0" class="flex-1 py-3 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white font-bold rounded-xl text-xs shadow-md shadow-sky-600/20 transition flex items-center justify-center space-x-2">
                                    <span>Submit Requisition Slip (RIS)</span>
                                    <span>&rarr;</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ============================================== -->
            <!-- RIGHT PANE: VIEW AVAILABLE STOCK DIRECTORY     -->
            <!-- ============================================== -->
            <div class="lg:col-span-7 space-y-4" :class="{ 'hidden lg:block': activeTab === 'cart' }">
                <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
                    
                    <!-- Section Header & In-Stock Counter -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                                <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                <span>Available Items Directory</span>
                            </h2>
                            <p class="text-xs text-slate-500">Browse live hospital stocks and add required quantities to your cart.</p>
                        </div>

                        <div class="text-xs font-mono font-bold text-sky-700 bg-sky-50 px-3 py-1 rounded-xl border border-sky-100 self-start sm:self-auto">
                            <span x-text="filteredProducts.length"></span> items available
                        </div>
                    </div>

                    <!-- Live Search Input -->
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" x-model="searchQuery" placeholder="Search available items by name, SKU, brand, or code..." class="w-full text-xs rounded-2xl border border-slate-300 pl-10 pr-4 py-3 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-slate-50/50">
                        <button type="button" @click="searchQuery = ''" x-show="searchQuery" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            &times;
                        </button>
                    </div>

                    <!-- Category Filter Pills -->
                    <div class="flex items-center space-x-2 overflow-x-auto pb-1 custom-scroll">
                        <button type="button" @click="selectedCategory = 'all'" 
                                :class="selectedCategory === 'all' ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition">
                            All Items
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" @click="selectedCategory = '{{ addslashes($cat) }}'" 
                                    :class="selectedCategory === '{{ addslashes($cat) }}' ? 'bg-sky-600 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition">
                                {{ $cat }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Available Items List -->
                    <div class="max-h-[640px] overflow-y-auto custom-scroll space-y-2.5 pr-1">
                        <template x-for="item in filteredProducts" :key="item.id">
                            <div class="p-3.5 rounded-2xl border border-slate-200 hover:border-sky-300 hover:shadow-xs transition bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 group">
                                
                                <!-- Item Info -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-xs font-bold text-slate-900 group-hover:text-sky-700 transition" x-text="item.name"></span>
                                        <template x-if="item.inventory_type === 'consignment'">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-700 border border-purple-200">CONSIGNMENT</span>
                                        </template>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono flex items-center space-x-2 mt-1">
                                        <span class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded" x-text="item.sku"></span>
                                        <span>&bull;</span>
                                        <span x-text="item.category"></span>
                                        <span>&bull;</span>
                                        <span class="text-emerald-700 font-bold" x-text="item.stock + ' ' + item.unit + ' in stock'"></span>
                                    </div>
                                </div>

                                <!-- Quantity Selector & Add Button -->
                                <div class="flex items-center space-x-2 self-end sm:self-auto flex-shrink-0">
                                    <div class="flex items-center space-x-1 bg-slate-50 p-1 rounded-xl border border-slate-200">
                                        <button type="button" @click="if(item.selectedQty > 1) item.selectedQty--" class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold flex items-center justify-center hover:bg-slate-100">
                                            -
                                        </button>
                                        <input type="number" min="1" :max="item.stock" x-model.number="item.selectedQty" class="w-12 text-center font-mono font-bold text-xs bg-transparent border-0 p-0 focus:ring-0">
                                        <button type="button" @click="if(item.selectedQty < item.stock) item.selectedQty++" class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold flex items-center justify-center hover:bg-slate-100">
                                            +
                                        </button>
                                    </div>

                                    <button type="button" @click="addToCart(item)" class="px-3.5 py-2 bg-slate-900 hover:bg-sky-600 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1 shadow-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span>Add to Cart</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Empty Search Results -->
                        <div x-show="filteredProducts.length === 0" class="py-12 text-center text-slate-400 space-y-2">
                            <svg class="w-12 h-12 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="font-bold text-slate-700">No supplies match your search criteria</p>
                            <p class="text-xs text-slate-400">Try adjusting your keyword or switching category filter.</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Floating Cart Button -->
        <button type="button" 
                @click="activeTab = 'cart'" 
                class="fixed bottom-6 right-6 z-40 bg-sky-600 hover:bg-sky-500 text-white px-5 py-3.5 rounded-full shadow-2xl shadow-sky-600/50 font-bold text-xs flex items-center space-x-2.5 transition transform hover:scale-105 group border-2 border-white/20">
            <svg class="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            <span>View Cart (<span x-text="cartTotalUnits"></span> units)</span>
            <span class="w-5 h-5 rounded-full bg-white text-sky-700 text-[11px] font-black flex items-center justify-center" x-text="cart.length"></span>
        </button>

    </main>

    <!-- Standalone Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} Roxas Memorial Provincial Hospital &bull; Capiz Provincial Government</p>
        <p class="text-[11px] text-slate-400 mt-1">Hospital Extensive Consignment &bull; Supply Management Section</p>
    </footer>

    <script>
        function publicRequisitionPortal() {
            const rawProducts = [
                @foreach($products as $p)
                {
                    id: {{ $p->id }},
                    sku: '{{ addslashes($p->sku) }}',
                    name: '{{ addslashes($p->name) }}',
                    category: '{{ addslashes($p->category?->name ?? 'General Supplies') }}',
                    inventory_type: '{{ addslashes($p->inventory_type) }}',
                    stock: {{ $p->total_stock }},
                    unit: '{{ addslashes($p->unit_of_measure) }}',
                    selectedQty: 1
                },
                @endforeach
            ];

            const departmentHeads = {
                'CSR': 'MIA BUENVENIDA',
                'DIALYSIS': 'EVANGELINE DETANOY',
                'PHARMACY OPD': 'SHARA PATRIA SANTOS',
                'PHARMACY MAIN': 'SHARA PATRIA SANTOS',
                'GUGMA DIALYSIS': 'STEPHEN ESPENOCILLA'
            };

            return {
                activeTab: 'items', // 'items' or 'cart'
                selectedDepartment: '{{ old('department_name', '') }}',
                requestedBy: '{{ old('requested_by', '') }}',
                purpose: '{{ old('purpose', '') }}',
                isEmergency: false,
                searchQuery: '',
                selectedCategory: 'all',
                products: rawProducts,
                cart: [],
                toast: {
                    show: false,
                    message: ''
                },
                init() {
                    if (this.selectedDepartment && departmentHeads[this.selectedDepartment]) {
                        if (!this.requestedBy) {
                            this.requestedBy = departmentHeads[this.selectedDepartment];
                        }
                    }
                },
                get currentDepartmentHead() {
                    return departmentHeads[this.selectedDepartment] || '';
                },
                onDepartmentChange() {
                    if (departmentHeads[this.selectedDepartment]) {
                        this.requestedBy = departmentHeads[this.selectedDepartment];
                    }
                },
                get filteredProducts() {
                    let items = this.products;

                    if (this.selectedCategory !== 'all') {
                        items = items.filter(p => p.category === this.selectedCategory);
                    }

                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase();
                        items = items.filter(p => 
                            p.name.toLowerCase().includes(q) ||
                            p.sku.toLowerCase().includes(q) ||
                            p.category.toLowerCase().includes(q)
                        );
                    }

                    return items;
                },
                get cartTotalItems() {
                    return this.cart.length;
                },
                get cartTotalUnits() {
                    return this.cart.reduce((sum, item) => sum + (parseInt(item.qty) || 0), 0);
                },
                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 2500);
                },
                addToCart(item) {
                    const qtyToAdd = parseInt(item.selectedQty) || 1;
                    const existing = this.cart.find(c => c.id === item.id);

                    if (existing) {
                        const newTotal = existing.qty + qtyToAdd;
                        if (newTotal > item.stock) {
                            existing.qty = item.stock;
                            this.showToast(`Set to maximum available stock (${item.stock} ${item.unit})`);
                        } else {
                            existing.qty = newTotal;
                            this.showToast(`Updated ${item.name} quantity to ${existing.qty}`);
                        }
                    } else {
                        this.cart.push({
                            id: item.id,
                            sku: item.sku,
                            name: item.name,
                            stock: item.stock,
                            unit: item.unit,
                            qty: Math.min(qtyToAdd, item.stock)
                        });
                        this.showToast(`Added ${item.name} to Requisition Cart!`);
                    }

                    item.selectedQty = 1;
                },
                incrementQty(item) {
                    if (item.qty < item.stock) {
                        item.qty++;
                    }
                },
                decrementQty(item) {
                    if (item.qty > 1) {
                        item.qty--;
                    }
                },
                removeItem(index) {
                    const removed = this.cart.splice(index, 1);
                    if (removed.length) {
                        this.showToast(`Removed item from cart.`);
                    }
                },
                onSubmitForm(e) {
                    if (!this.selectedDepartment) {
                        alert('Please select an authorized department (CSR, DIALYSIS, PHARMACY OPD, PHARMACY MAIN, or GUGMA DIALYSIS).');
                        e.preventDefault();
                        return;
                    }
                    if (!this.requestedBy.trim()) {
                        alert('Please provide the name of the requesting personnel or head.');
                        e.preventDefault();
                        return;
                    }
                    if (this.cart.length === 0) {
                        alert('Your Requisition Cart is empty. Please add items from the available inventory directory.');
                        e.preventDefault();
                        return;
                    }
                }
            };
        }
    </script>
</body>
</html>
