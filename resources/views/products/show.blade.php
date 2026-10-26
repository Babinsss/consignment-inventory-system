@extends('layouts.app')

@section('title', $product->name . ' - Stock & Batch Detail')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ adjustModal: false, selectedBatchId: '' }">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('products.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center">
            &larr; Back to Inventory
        </a>
        <div class="flex items-center space-x-2">
            <button type="button" @click="adjustModal = true" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                ⚡ Stock Adjustment / Disposal
            </button>
            <a href="{{ route('deliveries.create') }}" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                + Inward Delivery
            </a>
            <a href="{{ route('products.edit', $product) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300">
                Edit
            </a>
        </div>
    </div>

    <!-- Main Overview Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $product->item_type === 'pharmacy' ? 'bg-sky-100 text-sky-800' : 'bg-teal-100 text-teal-800' }}">
                        {{ $product->item_type === 'pharmacy' ? '💊 Pharmacy Stock' : '💉 Medical Supply' }}
                    </span>
                    @if($product->inventory_type === 'consignment')
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-100 text-purple-800">
                            📦 Consignment Agreement
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                            Owned Stock
                        </span>
                    @endif
                    @if(!$product->is_active)
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800">Inactive</span>
                    @endif
                </div>

                <h1 class="text-2xl font-extrabold text-slate-900 mt-2">{{ $product->name }}</h1>
                <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span>SKU: <strong class="font-mono text-slate-700">{{ $product->sku }}</strong></span>
                    @if($product->barcode)
                        <span>Barcode: <strong class="font-mono text-slate-700">{{ $product->barcode }}</strong></span>
                    @endif
                    @if($product->generic_name)
                        <span>Generic: <strong class="text-slate-700">{{ $product->generic_name }}</strong></span>
                    @endif
                    @if($product->brand_name)
                        <span>Brand: <strong class="text-slate-700">{{ $product->brand_name }}</strong></span>
                    @endif
                    <span>Category: <strong class="text-slate-700">{{ $product->category->name }}</strong></span>
                </div>
            </div>

            <!-- Current Stock Pill -->
            <div class="sm:text-right bg-slate-50 p-4 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 font-medium uppercase block">Total Stock on Hand</span>
                <span class="text-3xl font-extrabold {{ $product->total_stock <= $product->min_stock_alert ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $product->total_stock }}
                </span>
                <span class="text-xs text-slate-500 font-normal"> {{ $product->unit_of_measure }}</span>
                <div class="text-[11px] text-slate-400 mt-0.5">
                    Reorder Threshold: {{ $product->min_stock_alert }} | Max: {{ $product->max_stock_level }}
                </div>
            </div>
        </div>

        <!-- 4 Snapshot Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 uppercase font-medium">Default Purchase Cost</span>
                <div class="text-lg font-bold text-slate-900 mt-1">₱{{ number_format($product->default_purchase_price, 2) }}</div>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 uppercase font-medium">Selling / Charge Price</span>
                <div class="text-lg font-bold text-sky-700 mt-1">₱{{ number_format($product->selling_price, 2) }}</div>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 uppercase font-medium">Gross Markup Margin</span>
                @php
                    $markup = $product->default_purchase_price > 0 
                        ? round((($product->selling_price - $product->default_purchase_price) / $product->selling_price) * 100, 1) 
                        : 0;
                @endphp
                <div class="text-lg font-bold text-emerald-700 mt-1">{{ $markup }}%</div>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400 uppercase font-medium">Total Asset Valuation</span>
                <div class="text-lg font-bold text-indigo-700 mt-1">₱{{ number_format($product->inventory_value, 2) }}</div>
            </div>
        </div>

        <!-- Details Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-slate-600 bg-slate-50/50 p-4 rounded-xl border border-slate-100">
            <div>
                <span class="text-slate-400 block font-medium">Storage Requirements:</span>
                <span class="font-semibold text-slate-800">
                    @if($product->storage_condition === 'refrigerated_2_8c')
                        ❄️ Refrigerated Cold Chain (2°C - 8°C)
                    @elseif($product->storage_condition === 'controlled_substance')
                        🔒 Controlled Substance (Safe / Vault)
                    @elseif($product->storage_condition === 'cold_storage')
                        Deep Cold Storage (&lt;0°C)
                    @else
                        Room Temperature (15°C - 25°C)
                    @endif
                </span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Primary Supplier:</span>
                <span class="font-semibold text-slate-800">
                    {{ $product->supplier ? $product->supplier->name : 'Multiple / Not Specified' }}
                </span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Dosage / Formulation:</span>
                <span class="font-semibold text-slate-800">{{ $product->dosage_form ?: 'Standard' }}</span>
            </div>
        </div>

        @if($product->description)
        <div class="text-xs text-slate-600 bg-sky-50/50 p-3 rounded-lg border border-sky-100">
            <strong>Clinical & Handling Notes:</strong> {{ $product->description }}
        </div>
        @endif

    </div>

    <!-- Active Batches & Lots Section (FEFO - First Expire, First Out) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                    <span>Active Stock Batches (FEFO Dispensing Queue)</span>
                </h3>
                <p class="text-xs text-slate-500">Batches ordered by expiration date. Automatic dispensing will draw from the earliest expiring batch first.</p>
            </div>
            <a href="{{ route('deliveries.create') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">+ Receive New Batch</a>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Batch / Lot #</th>
                        <th class="py-2.5 px-3">Delivery Ref</th>
                        <th class="py-2.5 px-3">Mfg Date</th>
                        <th class="py-2.5 px-3">Expiry Date</th>
                        <th class="py-2.5 px-3">Expiry Countdown</th>
                        <th class="py-2.5 px-3 text-right">Received</th>
                        <th class="py-2.5 px-3 text-right">Remaining</th>
                        <th class="py-2.5 px-3 text-right">Batch Unit Cost</th>
                        <th class="py-2.5 px-3 text-right">Valuation</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->batches as $batch)
                    <tr class="hover:bg-slate-50 {{ $batch->is_expired ? 'bg-red-50/40' : '' }}">
                        <td class="py-3 px-3 font-mono font-bold text-slate-900">
                            {{ $batch->batch_number }}
                            @if($batch->lot_number)
                                <span class="block text-[10px] text-slate-400 font-normal">Lot: {{ $batch->lot_number }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-600">
                            @if($batch->delivery)
                                <a href="{{ route('deliveries.show', $batch->delivery) }}" class="text-sky-600 hover:underline">
                                    {{ $batch->delivery->delivery_number }}
                                </a>
                            @else
                                <span class="text-slate-400">Opening Balance</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-600">
                            {{ $batch->manufacture_date ? $batch->manufacture_date->format('M Y') : '—' }}
                        </td>
                        <td class="py-3 px-3 font-semibold text-slate-900 whitespace-nowrap">
                            {{ $batch->expiry_date->format('M d, Y') }}
                        </td>
                        <td class="py-3 px-3 whitespace-nowrap">
                            @if($batch->days_until_expiry < 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">
                                    EXPIRED ({{ abs($batch->days_until_expiry) }}d ago)
                                </span>
                            @elseif($batch->days_until_expiry <= 30)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                    Critical: {{ $batch->days_until_expiry }} days left
                                </span>
                            @elseif($batch->days_until_expiry <= 90)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800">
                                    Warning: {{ $batch->days_until_expiry }} days left
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700">
                                    Safe ({{ $batch->days_until_expiry }} days)
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-right text-slate-500">
                            {{ $batch->quantity_received }}
                        </td>
                        <td class="py-3 px-3 text-right font-extrabold text-slate-900">
                            {{ $batch->quantity_remaining }}
                        </td>
                        <td class="py-3 px-3 text-right text-slate-700">
                            ₱{{ number_format($batch->unit_purchase_cost, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-bold text-slate-900">
                            ₱{{ number_format($batch->quantity_remaining * $batch->unit_purchase_cost, 2) }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($batch->quantity_remaining <= 0)
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] rounded-full font-medium">Depleted</span>
                            @elseif($batch->is_expired)
                                <span class="px-2 py-0.5 bg-red-100 text-red-800 text-[10px] rounded-full font-bold">Expired</span>
                            @else
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] rounded-full font-medium">Active</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-6 text-center text-slate-400">
                            No batches recorded for this item yet. Record a delivery to add batches.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Movements & Audit Trail -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Stock Movement Audit History</h3>
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">Date / Time</th>
                        <th class="py-2.5 px-3">Movement Type</th>
                        <th class="py-2.5 px-3">Batch #</th>
                        <th class="py-2.5 px-3 text-right">Qty</th>
                        <th class="py-2.5 px-3 text-right">Balance Before</th>
                        <th class="py-2.5 px-3 text-right">Balance After</th>
                        <th class="py-2.5 px-3">Reference / Reason</th>
                        <th class="py-2.5 px-3">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($product->stockMovements as $move)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">
                            {{ $move->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="py-2.5 px-3 font-semibold">
                            @if($move->movement_type === 'delivery_in')
                                <span class="text-emerald-700">↓ Delivery In</span>
                            @elseif($move->movement_type === 'dispense_out')
                                <span class="text-sky-700">↑ Dispensed / Sold</span>
                            @elseif($move->movement_type === 'disposal_expired')
                                <span class="text-rose-700">✕ Expired Disposal</span>
                            @elseif($move->movement_type === 'adjustment_add')
                                <span class="text-teal-700">+ Audit Found</span>
                            @else
                                <span class="text-amber-700">- Adjustment Deduct</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 font-mono text-slate-700">
                            {{ $move->batch ? $move->batch->batch_number : '—' }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-bold {{ $move->quantity > 0 ? 'text-emerald-600' : 'text-slate-900' }}">
                            {{ $move->quantity > 0 ? '+' . $move->quantity : $move->quantity }}
                        </td>
                        <td class="py-2.5 px-3 text-right text-slate-500">{{ $move->balance_before }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ $move->balance_after }}</td>
                        <td class="py-2.5 px-3 text-slate-600">{{ $move->reason ?: '—' }}</td>
                        <td class="py-2.5 px-3 text-slate-500">{{ $move->user_name ?: 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-slate-400">No stock movements recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Stock Adjustment / Expiry Disposal Modal -->
    <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="adjustModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Adjust Stock or Write-off Expired Items</h3>
                <button type="button" @click="adjustModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form method="POST" action="{{ route('products.adjust', $product) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Batch *</label>
                    <select name="batch_id" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="">-- Select Batch to Adjust --</option>
                        @foreach($product->batches->where('quantity_remaining', '>', 0) as $b)
                            <option value="{{ $b->id }}">
                                {{ $b->batch_number }} (Rem: {{ $b->quantity_remaining }} | Exp: {{ $b->expiry_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Adjustment Type *</label>
                    <select name="adjustment_type" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="disposal_expired">Expired Stock Disposal / Condemn (Deduct)</option>
                        <option value="adjustment_deduct">Damaged / Lost Stock (Deduct)</option>
                        <option value="adjustment_add">Physical Count Audit Discrepancy Found (Add)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity *</label>
                    <input type="number" min="1" step="1" name="quantity" required placeholder="Units" class="w-full text-xs font-bold rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Audit Reason / Disposal Authorization *</label>
                    <input type="text" name="reason" required placeholder="e.g. Expired batch condemned per DOH memo, Broken ampule" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Authorized Staff *</label>
                    <input type="text" name="user_name" value="Pharmacist on Duty" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="adjustModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg shadow-xs">✓ Post Adjustment</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
