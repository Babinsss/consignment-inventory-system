@extends('layouts.app')

@section('title', 'General Form No. 18 - Stock Card')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    products: {{ json_encode($productsJson) }},
    searchQuery: '',
    dropdownOpen: false,
    selectedConsignor: '{{ $selectedConsignorId }}',
    directoryModal: false,
    modalSearch: '',
    modalConsignor: '',
    selectedId: {{ $selectedProduct ? $selectedProduct->id : 'null' }},

    get filteredProducts() {
        let list = this.products;
        if (this.selectedConsignor) {
            list = list.filter(p => p.supplier_id == this.selectedConsignor);
        }
        if (this.searchQuery.trim()) {
            const q = this.searchQuery.toLowerCase().trim();
            list = list.filter(p => 
                p.name.toLowerCase().includes(q) || 
                p.sku.toLowerCase().includes(q) || 
                p.supplier_name.toLowerCase().includes(q)
            );
        }
        return list;
    },

    get modalFilteredProducts() {
        let list = this.products;
        if (this.modalConsignor) {
            list = list.filter(p => p.supplier_id == this.modalConsignor);
        }
        if (this.modalSearch.trim()) {
            const q = this.modalSearch.toLowerCase().trim();
            list = list.filter(p => 
                p.name.toLowerCase().includes(q) || 
                p.sku.toLowerCase().includes(q) || 
                p.supplier_name.toLowerCase().includes(q)
            );
        }
        return list;
    },

    selectProduct(id) {
        this.selectedId = id;
        this.dropdownOpen = false;
        const url = new URL(window.location.href);
        url.searchParams.set('product_id', id);
        if (this.selectedConsignor) {
            url.searchParams.set('consignor_id', this.selectedConsignor);
        }
        window.location.href = url.toString();
    },

    filterByConsignor(supId) {
        this.selectedConsignor = supId;
        const url = new URL(window.location.href);
        if (supId) {
            url.searchParams.set('consignor_id', supId);
        } else {
            url.searchParams.delete('consignor_id');
        }
        url.searchParams.delete('product_id');
        window.location.href = url.toString();
    }
}">

    <!-- Top Action & Navigation Header -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-purple-700 uppercase tracking-widest mb-1">
                <span class="inline-block w-2 h-2 rounded-full bg-purple-600"></span>
                <span>Republic of the Philippines &bull; Capiz Provincial Government</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Government Stock Card (General Form No. 18)</h1>
            <p class="text-xs text-slate-500">Official Philippine government stock ledger tracking inward deliveries, departmental issues, and running balances.</p>
        </div>
        <div class="flex items-center space-x-2 self-start sm:self-auto flex-wrap gap-2">
            <button type="button" @click="directoryModal = true" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>Catalog Directory ({{ $products->count() }})</span>
            </button>
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Print Stock Card</span>
            </button>
        </div>
    </div>

    <!-- Enhanced Searchable Stock Item Selector -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs space-y-4 no-print">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Quick Consignor Filter Dropdown -->
            <div class="flex items-center space-x-2 w-full lg:w-auto">
                <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Filter Consignor:</label>
                <select x-model="selectedConsignor" @change="filterByConsignor($event.target.value)" class="text-xs rounded-xl border border-slate-300 px-3 py-2.5 bg-slate-50 font-medium text-slate-800 focus:ring-2 focus:ring-purple-500 focus:bg-white w-full sm:w-64">
                    <option value="">All Consignors ({{ $consignors->sum('products_count') }} items)</option>
                    @foreach($consignors as $c)
                        <option value="{{ $c->id }}" {{ $selectedConsignorId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->products_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Previous / Next Item Flip Buttons -->
            <div class="flex items-center space-x-2 justify-end">
                @if($prevProductId)
                    <a href="{{ route('hospital.stock-card', array_filter(['product_id' => $prevProductId, 'consignor_id' => $selectedConsignorId])) }}"
                       class="px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition inline-flex items-center space-x-1"
                       title="Flip to previous stock card">
                        <span>&larr; Previous</span>
                    </a>
                @else
                    <span class="px-3 py-2 text-xs font-semibold text-slate-300 bg-slate-50 rounded-xl cursor-not-allowed">&larr; Previous</span>
                @endif

                @if($nextProductId)
                    <a href="{{ route('hospital.stock-card', array_filter(['product_id' => $nextProductId, 'consignor_id' => $selectedConsignorId])) }}"
                       class="px-3 py-2 text-xs font-semibold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-xl transition inline-flex items-center space-x-1"
                       title="Flip to next stock card">
                        <span>Next Card &rarr;</span>
                    </a>
                @else
                    <span class="px-3 py-2 text-xs font-semibold text-slate-300 bg-slate-50 rounded-xl cursor-not-allowed">Next &rarr;</span>
                @endif
            </div>
        </div>

        <!-- Interactive Searchable Combobox -->
        <div class="relative" @click.outside="dropdownOpen = false">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1.5">
                Active Consigned Stock Item:
            </label>

            <!-- Search Bar Trigger Button / Display -->
            <div class="relative">
                <div @click="dropdownOpen = !dropdownOpen; $nextTick(() => { if(dropdownOpen) $refs.searchInput.focus() })" 
                     class="w-full flex items-center justify-between p-3.5 rounded-2xl border border-slate-300 hover:border-purple-500 bg-slate-50 hover:bg-white cursor-pointer transition shadow-xs group">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </span>
                        @if($selectedProduct)
                            <div class="truncate text-left">
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded-md">
                                        {{ $selectedProduct->sku }}
                                    </span>
                                    <span class="font-bold text-slate-900 text-xs sm:text-sm truncate">
                                        {{ $selectedProduct->name }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 flex items-center space-x-2 flex-wrap">
                                    <span>Consignor: <strong class="text-slate-700">{{ $selectedProduct->supplier?->name }}</strong></span>
                                    <span>&bull;</span>
                                    <span>Unit Cost: <strong class="text-emerald-700">&#8369;{{ number_format($selectedProduct->default_purchase_price, 2) }}</strong></span>
                                    <span>&bull;</span>
                                    <span>Unit: <strong class="text-slate-700 uppercase">{{ $selectedProduct->unit_of_measure }}</strong></span>
                                </div>
                            </div>
                        @else
                            <span class="text-xs text-slate-400">Click to choose or search a medical supply...</span>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-2 ml-2 flex-shrink-0">
                        <span class="text-xs font-semibold text-purple-700 group-hover:underline hidden sm:inline">Search &amp; Change</span>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-purple-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Floating Search Dropdown -->
            <div x-show="dropdownOpen" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 class="absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden divide-y divide-slate-100">
                
                <!-- Live Search Input -->
                <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-purple-600 ml-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           x-model="searchQuery" 
                           x-ref="searchInput"
                           @keydown.enter.prevent="if(filteredProducts.length > 0) selectProduct(filteredProducts[0].id)"
                           @keydown.escape="dropdownOpen = false"
                           placeholder="Type item name, SKU, or supplier..." 
                           class="w-full text-xs bg-transparent border-0 focus:ring-0 text-slate-800 placeholder-slate-400 font-medium py-1">
                    <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="text-slate-400 hover:text-slate-600 text-xs font-bold px-1.5">&times;</button>
                </div>

                <!-- Scrollable Items List -->
                <div class="max-h-72 overflow-y-auto divide-y divide-slate-50 text-xs custom-scroll">
                    <template x-for="item in filteredProducts" :key="item.id">
                        <div @click="selectProduct(item.id)"
                             class="p-3 hover:bg-purple-50/70 cursor-pointer transition flex items-center justify-between group"
                             :class="item.id === selectedId ? 'bg-purple-50 ring-1 ring-inset ring-purple-200' : ''">
                            <div class="flex items-center space-x-2.5 overflow-hidden pr-2">
                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 group-hover:bg-purple-100 group-hover:text-purple-800"
                                      x-text="item.sku"></span>
                                <div class="truncate">
                                    <span class="font-bold text-slate-900 group-hover:text-purple-900 block truncate" x-text="item.name"></span>
                                    <span class="text-[10px] text-slate-500" x-text="item.supplier_name"></span>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="font-mono font-bold text-emerald-700 block" x-text="'&#8369;' + item.cost.toFixed(2)"></span>
                                <span class="text-[9px] text-slate-400 uppercase" x-text="item.unit"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Empty Results State -->
                    <div x-show="filteredProducts.length === 0" class="p-8 text-center text-slate-400">
                        <p class="text-xs font-semibold text-slate-600">No medical supplies match your search</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Try searching with a shorter keyword or change consignor filter.</p>
                    </div>
                </div>

                <!-- Footer Summary Strip -->
                <div class="p-2.5 bg-slate-50 text-[11px] text-slate-500 flex items-center justify-between px-4">
                    <span>Showing <strong class="text-slate-800" x-text="filteredProducts.length"></strong> of <span x-text="products.length"></span> consigned supplies</span>
                    <button type="button" @click="dropdownOpen = false" class="text-xs text-purple-700 font-bold hover:underline">Close &times;</button>
                </div>
            </div>
        </div>
    </div>

    @if($selectedProduct)
    <!-- Quick Balance Metric Strip (Crucial for Mobile & Quick Checks!) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 no-print">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">On-Hand Balance</span>
            <div class="text-xl sm:text-2xl font-black text-slate-900 mt-1">
                {{ $selectedProduct->total_stock }}
                <span class="text-xs font-normal text-slate-500 uppercase">{{ $selectedProduct->unit_of_measure }}</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Stock Valuation</span>
            <div class="text-xl sm:text-2xl font-black text-emerald-700 mt-1 font-mono">
                &#8369;{{ number_format($selectedProduct->total_stock * $selectedProduct->default_purchase_price, 2) }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Unit Purchase Cost</span>
            <div class="text-xl sm:text-2xl font-bold text-slate-800 mt-1 font-mono">
                &#8369;{{ number_format($selectedProduct->default_purchase_price, 2) }}
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Reorder Threshold</span>
            <div class="text-xl sm:text-2xl font-bold {{ $selectedProduct->total_stock <= $selectedProduct->min_stock_alert ? 'text-rose-600' : 'text-slate-800' }} mt-1">
                {{ $selectedProduct->min_stock_alert }}
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $selectedProduct->total_stock <= $selectedProduct->min_stock_alert ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                    {{ $selectedProduct->total_stock <= $selectedProduct->min_stock_alert ? 'REORDER' : 'SAFE' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Official General Form No. 18 Document Card -->
    <div class="bg-white rounded-2xl p-5 sm:p-8 border border-slate-300 shadow-md space-y-4 printable-sheet">

        <!-- Form 18 Header -->
        <div class="flex justify-between items-start text-xs border-b border-slate-300 pb-2">
            <div>
                <span class="font-serif font-black text-slate-900 tracking-wider text-sm">GENERAL FORM NO. 18</span>
                <span class="block text-[10px] text-slate-500 italic">Revised January 1992</span>
            </div>
            <div class="text-right">
                <span class="font-mono text-xs font-semibold text-slate-700">STOCK NO.: <strong class="text-slate-900 bg-slate-100 px-2 py-0.5 rounded">{{ $selectedProduct->sku }}</strong></span>
            </div>
        </div>

        <div class="text-center py-1">
            <h2 class="text-lg sm:text-2xl font-serif font-black tracking-[0.2em] text-slate-900 uppercase">S T O C K &nbsp;&nbsp; C A R D</h2>
        </div>

        <!-- Agency & Description Header Strip -->
        <div class="border border-slate-400 text-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 border-b border-slate-400 p-2.5 gap-3">
                <div>
                    <span class="text-slate-500 font-medium">AGENCY / OFFICE:</span>
                    <strong class="text-slate-900 ml-1">ROXAS MEMORIAL PROVINCIAL HOSPITAL</strong>
                </div>
                <div class="flex justify-between items-center">
                    <div><span class="text-slate-500 font-medium">LOCATION:</span> <strong class="text-slate-900">CENTRAL SUPPLY ROOM (CSR) / DIALYSIS</strong></div>
                    <div><span class="text-slate-500 font-medium">REORDER POINT:</span> <strong class="text-rose-700 font-mono">{{ $selectedProduct->min_stock_alert }}</strong></div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 p-2.5 gap-3 bg-slate-50">
                <div>
                    <span class="text-slate-500 font-medium">DESCRIPTION:</span>
                    <strong class="text-slate-900 ml-1 uppercase text-sm tracking-tight">{{ $selectedProduct->name }}</strong>
                    <span class="block text-[11px] text-slate-600 mt-0.5 font-medium">
                        Consignor: <strong class="text-purple-900">{{ $selectedProduct->supplier?->name }}</strong> &bull; Unit Cost: <strong>&#8369;{{ number_format($selectedProduct->default_purchase_price, 2) }}</strong>
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <div><span class="text-slate-500 font-medium">UNIT OF ISSUE:</span> <strong class="text-slate-900 uppercase font-mono">{{ $selectedProduct->unit_of_measure }}</strong></div>
                    <div><span class="text-[10px] font-mono font-bold bg-purple-100 text-purple-900 border border-purple-200 px-2.5 py-0.5 rounded-full">HECS CONSIGNMENT</span></div>
                </div>
            </div>
        </div>

        <!-- 3-Way General Form 18 Table: Received / Issued / Balance -->
        <div class="overflow-x-auto custom-scroll has-scroll-hint border border-slate-400 rounded-lg">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100 text-slate-800 font-bold border-b border-slate-400 text-center sticky top-0">
                    <tr>
                        <th rowspan="2" class="py-2.5 px-2 border-r border-slate-400 w-24">DATE</th>
                        <th rowspan="2" class="py-2.5 px-2 border-r border-slate-400 w-28">REFERENCE</th>
                        <th rowspan="2" class="py-2.5 px-3 border-r border-slate-400 text-left min-w-[170px]">FROM WHOM RECEIVED OR TO WHOM ISSUED</th>
                        <th colspan="3" class="py-1 px-2 border-r border-slate-400 bg-emerald-50 text-emerald-900">RECEIVED</th>
                        <th colspan="3" class="py-1 px-2 border-r border-slate-400 bg-sky-50 text-sky-900">ISSUED</th>
                        <th colspan="3" class="py-1 px-2 bg-amber-50 text-amber-900">BALANCE</th>
                    </tr>
                    <tr class="border-t border-slate-400 text-[10px]">
                        <th class="py-1 px-2 border-r border-slate-400 text-right w-12 bg-emerald-50/70">QTY</th>
                        <th class="py-1 px-2 border-r border-slate-400 text-right w-16 bg-emerald-50/70">UNIT COST</th>
                        <th class="py-1 px-2 border-r border-slate-400 text-right w-20 bg-emerald-50/70">TOTAL</th>
                        <th class="py-1 px-2 border-r border-slate-400 text-right w-12 bg-sky-50/70">QTY</th>
                        <th class="py-1 px-2 border-r border-slate-400 text-right w-16 bg-sky-50/70">UNIT COST</th>
                        <th class="py-1 px-2 border-r border-slate-400 text-right w-20 bg-sky-50/70">TOTAL</th>
                        <th class="py-1 px-2 border-r border-slate-400 text-right w-14 bg-amber-50/70">QTY</th>
                        <th class="py-1 px-2 border-r border-slate-400 text-right w-16 bg-amber-50/70">UNIT COST</th>
                        <th class="py-1 px-2 text-right w-20 bg-amber-50/70">TOTAL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-xs font-mono">
                    @forelse($ledger as $row)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2 px-2 border-r border-slate-300 text-slate-600 whitespace-nowrap">{{ $row['date'] }}</td>
                        <td class="py-2 px-2 border-r border-slate-300 font-semibold text-slate-800">{{ $row['reference'] }}</td>
                        <td class="py-2 px-3 border-r border-slate-300 font-sans font-medium text-slate-900">{{ $row['party'] }}</td>

                        <!-- Received -->
                        <td class="py-2 px-2 border-r border-slate-300 text-right font-bold text-emerald-700 bg-emerald-50/30">{{ $row['rec_qty'] }}</td>
                        <td class="py-2 px-2 border-r border-slate-300 text-right text-slate-600 bg-emerald-50/30">{{ $row['rec_cost'] }}</td>
                        <td class="py-2 px-2 border-r border-slate-300 text-right font-semibold text-emerald-800 bg-emerald-50/30">{{ $row['rec_total'] }}</td>

                        <!-- Issued -->
                        <td class="py-2 px-2 border-r border-slate-300 text-right font-bold text-sky-700 bg-sky-50/30">{{ $row['iss_qty'] }}</td>
                        <td class="py-2 px-2 border-r border-slate-300 text-right text-slate-600 bg-sky-50/30">{{ $row['iss_cost'] }}</td>
                        <td class="py-2 px-2 border-r border-slate-300 text-right font-semibold text-sky-800 bg-sky-50/30">{{ $row['iss_total'] }}</td>

                        <!-- Balance -->
                        <td class="py-2 px-2 border-r border-slate-300 text-right font-black text-slate-900 bg-amber-50/50">{{ $row['bal_qty'] }}</td>
                        <td class="py-2 px-2 border-r border-slate-300 text-right text-slate-600 bg-amber-50/50">{{ $row['bal_cost'] }}</td>
                        <td class="py-2 px-2 text-right font-black text-slate-900 bg-amber-50/50">{{ $row['bal_total'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-12 text-center text-slate-400 font-sans italic">
                            No stock card ledger entries recorded yet for this item. Receive a delivery to start this ledger.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Official Signatures Block -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-6 text-xs text-center">
            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Custodial Logged by:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">ALIANA MARIE A. DULA</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Administrative Aide III / Consignment Staff</div>
            </div>

            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Verified Correct:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">JOEMAR A. DADIVAS, RN, CNN, MAN</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Nurse Supervisor / CSR In-Charge</div>
            </div>

            <div>
                <p class="text-left font-bold text-slate-700 mb-6">Approved by:</p>
                <div class="border-b border-slate-400 pb-1 font-bold text-slate-900">FLORENCIO O. LUCHING, JR., MD, FPCS</div>
                <div class="text-slate-500 text-[10px] mt-0.5">Chief of Hospital II</div>
            </div>
        </div>

    </div>
    @endif

    <!-- Catalog Directory Modal -->
    <div x-show="directoryModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto no-print" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div @click="directoryModal = false" class="fixed inset-0 bg-slate-900/60 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-200">
                <div class="bg-gradient-to-r from-purple-900 to-indigo-900 p-5 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold tracking-tight">Consigned Medical Supplies Directory</h3>
                        <p class="text-xs text-purple-200">Roxas Memorial Provincial Hospital &bull; Total {{ $products->count() }} Stock Cards</p>
                    </div>
                    <button type="button" @click="directoryModal = false" class="text-purple-200 hover:text-white font-bold text-xl p-1">&times;</button>
                </div>

                <!-- Modal Filter Bar -->
                <div class="p-4 bg-slate-50 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 relative">
                        <input type="text" x-model="modalSearch" placeholder="Type item name, SKU, or supplier..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 text-slate-800 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <select x-model="modalConsignor" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 text-slate-800 focus:ring-2 focus:ring-purple-500">
                            <option value="">All Consignors</option>
                            @foreach($consignors as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Modal Table -->
                <div class="max-h-96 overflow-y-auto p-4 custom-scroll">
                    <table class="w-full text-left text-xs divide-y divide-slate-200">
                        <thead class="bg-slate-100 text-slate-700 font-bold sticky top-0">
                            <tr>
                                <th class="p-2.5">Stock No.</th>
                                <th class="p-2.5">Description</th>
                                <th class="p-2.5 hidden sm:table-cell">Consignor</th>
                                <th class="p-2.5 text-right">Cost (&#8369;)</th>
                                <th class="p-2.5 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in modalFilteredProducts" :key="item.id">
                                <tr class="hover:bg-purple-50/50 transition">
                                    <td class="p-2.5 font-mono font-bold text-purple-800" x-text="item.sku"></td>
                                    <td class="p-2.5 font-semibold text-slate-900" x-text="item.name"></td>
                                    <td class="p-2.5 text-slate-600 hidden sm:table-cell" x-text="item.supplier_name"></td>
                                    <td class="p-2.5 text-right font-mono font-bold text-emerald-700" x-text="'&#8369;' + item.cost.toFixed(2)"></td>
                                    <td class="p-2.5 text-center">
                                        <button type="button" @click="directoryModal = false; selectProduct(item.id)" class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-xs font-bold transition">
                                            Open Card
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Modal Footer -->
                <div class="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between px-5 text-xs text-slate-500">
                    <span>Showing <strong x-text="modalFilteredProducts.length"></strong> item(s)</span>
                    <button type="button" @click="directoryModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-xl font-bold transition">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
