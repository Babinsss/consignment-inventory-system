@extends('layouts.app')

@section('title', 'Medical Supplies & Pharmacy Stocks Inventory')

@section('content')
<div class="space-y-6">

    <!-- Page Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Medical Supplies & Pharmacy Inventory</h1>
            <p class="text-xs sm:text-sm text-slate-500">Track stock on hand, consignment agreements, FEFO batches, and inventory valuation.</p>
        </div>
        <div class="flex items-center space-x-2 self-start sm:self-auto">
            <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs sm:text-sm font-semibold shadow-sm shadow-sky-600/20 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Item</span>
            </a>
        </div>
    </div>

    <!-- Quick Filter Category Navigation Strip -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 text-xs custom-scroll">
        <a href="{{ route('products.index') }}" class="px-3.5 py-1.5 rounded-xl font-medium whitespace-nowrap transition {{ !request()->hasAny(['item_type', 'inventory_type', 'stock_status', 'expiry_filter']) ? 'bg-sky-600 text-white shadow-xs font-semibold' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            All Stock ({{ \App\Models\Product::count() }})
        </a>
        <a href="{{ route('products.index', ['item_type' => 'pharmacy']) }}" class="px-3.5 py-1.5 rounded-xl font-medium whitespace-nowrap transition {{ request('item_type') === 'pharmacy' ? 'bg-sky-600 text-white shadow-xs font-semibold' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            Pharmacy ({{ $totalPharmacyCount }})
        </a>
        <a href="{{ route('products.index', ['item_type' => 'medical_supply']) }}" class="px-3.5 py-1.5 rounded-xl font-medium whitespace-nowrap transition {{ request('item_type') === 'medical_supply' ? 'bg-sky-600 text-white shadow-xs font-semibold' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            Medical Supplies ({{ $totalMedicalCount }})
        </a>
        <a href="{{ route('products.index', ['inventory_type' => 'consignment']) }}" class="px-3.5 py-1.5 rounded-xl font-medium whitespace-nowrap transition {{ request('inventory_type') === 'consignment' ? 'bg-purple-600 text-white shadow-xs font-semibold' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            Consignment Stock ({{ $totalConsignmentCount }})
        </a>
        <a href="{{ route('products.index', ['stock_status' => 'low_stock']) }}" class="px-3.5 py-1.5 rounded-xl font-medium whitespace-nowrap transition {{ request('stock_status') === 'low_stock' ? 'bg-rose-600 text-white shadow-xs font-semibold' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200' }}">
            Low Stock / Reorder ({{ $totalLowStockCount }})
        </a>
        <a href="{{ route('products.index', ['expiry_filter' => 'critical']) }}" class="px-3.5 py-1.5 rounded-xl font-medium whitespace-nowrap transition {{ request('expiry_filter') === 'critical' ? 'bg-amber-600 text-white shadow-xs font-semibold' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' }}">
            Expiring Soon (&lt;30d)
        </a>
    </div>

    <!-- Advanced Search & Filter Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @if(request('item_type'))
                <input type="hidden" name="item_type" value="{{ request('item_type') }}">
            @endif
            @if(request('inventory_type'))
                <input type="hidden" name="inventory_type" value="{{ request('inventory_type') }}">
            @endif

            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by SKU, Brand Name, Generic Name..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>

            <div>
                <select name="category_id" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="storage_condition" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    <option value="">All Storage Conditions</option>
                    <option value="room_temperature" {{ request('storage_condition') == 'room_temperature' ? 'selected' : '' }}>Room Temp</option>
                    <option value="refrigerated_2_8c" {{ request('storage_condition') == 'refrigerated_2_8c' ? 'selected' : '' }}>Refrigerated 2-8&deg;C</option>
                    <option value="cold_storage" {{ request('storage_condition') == 'cold_storage' ? 'selected' : '' }}>Cold Storage</option>
                    <option value="controlled_substance" {{ request('storage_condition') == 'controlled_substance' ? 'selected' : '' }}>Controlled Drug</option>
                </select>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl px-4 py-2.5 transition">
                    Search
                </button>
                <a href="{{ route('products.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center justify-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto custom-scroll">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-100 sticky top-0">
                    <tr>
                        <th class="py-3 px-4">Item &amp; SKU</th>
                        <th class="py-3 px-4">Category &amp; Type</th>
                        <th class="py-3 px-4 hidden md:table-cell">Storage</th>
                        <th class="py-3 px-4 text-center hidden sm:table-cell">Unit</th>
                        <th class="py-3 px-4 text-center">Stock on Hand</th>
                        <th class="py-3 px-4 text-right hidden lg:table-cell">Cost Price</th>
                        <th class="py-3 px-4 text-right">Selling Price</th>
                        <th class="py-3 px-4 text-right hidden xl:table-cell">Valuation</th>
                        <th class="py-3 px-4 text-center hidden md:table-cell">Expiry Watch</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $prod)
                    <tr class="hover:bg-slate-50/80 transition">
                        <!-- Name & SKU -->
                        <td class="py-3 px-4">
                            <a href="{{ route('products.show', $prod) }}" class="font-bold text-slate-900 hover:text-sky-600 hover:underline block leading-tight">
                                {{ $prod->name }}
                            </a>
                            <div class="text-[10px] text-slate-400 mt-0.5 flex items-center space-x-1.5 flex-wrap">
                                <span class="font-mono text-slate-500 font-semibold">{{ $prod->sku }}</span>
                                @if($prod->generic_name)
                                    <span>&bull; {{ Str::limit($prod->generic_name, 25) }}</span>
                                @endif
                                @if($prod->inventory_type === 'consignment')
                                    <span class="px-1.5 py-0.2 rounded bg-purple-100 text-purple-800 font-bold text-[9px] uppercase">Consignment</span>
                                @endif
                            </div>
                        </td>

                        <!-- Category & Type -->
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $prod->item_type === 'pharmacy' ? 'bg-sky-100 text-sky-800' : 'bg-teal-100 text-teal-800' }}">
                                {{ $prod->item_type === 'pharmacy' ? 'Pharmacy' : 'Supply' }}
                            </span>
                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ Str::limit($prod->category->name, 16) }}</span>
                        </td>

                        <!-- Storage -->
                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap hidden md:table-cell">
                            @if($prod->storage_condition === 'refrigerated_2_8c')
                                <span class="inline-flex items-center text-[10px] font-semibold text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded-full border border-cyan-200">2-8&deg;C Cold Chain</span>
                            @elseif($prod->storage_condition === 'controlled_substance')
                                <span class="inline-flex items-center text-[10px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">Controlled</span>
                            @else
                                <span class="text-[10px] text-slate-500">Room Temp</span>
                            @endif
                        </td>

                        <!-- Unit -->
                        <td class="py-3 px-4 text-center text-slate-600 capitalize whitespace-nowrap hidden sm:table-cell">
                            {{ $prod->unit_of_measure }}
                        </td>

                        <!-- Total Stock & Status Badge -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="text-sm font-extrabold {{ $prod->total_stock <= $prod->min_stock_alert ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ $prod->total_stock }}
                            </span>
                            @if($prod->total_stock <= 0)
                                <span class="block text-[9px] font-bold text-rose-600 uppercase">Out of Stock</span>
                            @elseif($prod->total_stock <= $prod->min_stock_alert)
                                <span class="block text-[9px] font-bold text-amber-600 uppercase">Low (Min: {{ $prod->min_stock_alert }})</span>
                            @else
                                <span class="block text-[9px] text-slate-400">Min: {{ $prod->min_stock_alert }}</span>
                            @endif
                        </td>

                        <!-- Cost -->
                        <td class="py-3 px-4 text-right text-slate-700 whitespace-nowrap hidden lg:table-cell font-mono">
                            &#8369;{{ number_format($prod->default_purchase_price, 2) }}
                        </td>

                        <!-- Selling -->
                        <td class="py-3 px-4 text-right font-semibold text-slate-900 whitespace-nowrap font-mono">
                            &#8369;{{ number_format($prod->selling_price, 2) }}
                        </td>

                        <!-- Valuation -->
                        <td class="py-3 px-4 text-right font-bold text-slate-900 whitespace-nowrap hidden xl:table-cell font-mono">
                            &#8369;{{ number_format($prod->inventory_value, 2) }}
                        </td>

                        <!-- Expiry Status -->
                        <td class="py-3 px-4 text-center whitespace-nowrap hidden md:table-cell">
                            @if($prod->expiry_alert === 'expired')
                                <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded-full text-[10px] font-bold">EXPIRED</span>
                            @elseif($prod->expiry_alert === 'critical')
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-[10px] font-bold">&le; 30 Days</span>
                            @elseif($prod->expiry_alert === 'warning')
                                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-[10px] font-bold">&le; 90 Days</span>
                            @elseif($prod->nearest_expiry)
                                <span class="text-[10px] text-emerald-700 font-semibold">Safe</span>
                            @else
                                <span class="text-[10px] text-slate-400">&mdash;</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                            <a href="{{ route('products.show', $prod) }}" class="inline-block px-2.5 py-1 text-slate-600 hover:text-sky-700 hover:bg-sky-50 rounded-lg text-xs font-semibold transition" title="View Batches & Movements">
                                View
                            </a>
                            <a href="{{ route('products.edit', $prod) }}" class="inline-block px-2.5 py-1 text-slate-600 hover:text-indigo-700 hover:bg-indigo-50 rounded-lg text-xs font-semibold transition" title="Edit Item Details">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400 text-xs">
                            No medical items or stocks found matching the criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
