@extends('layouts.app')

@section('title', 'Add New Inventory Item')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('products.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center mb-1">
                &larr; Back to Inventory
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add New Medical Supply or Pharmacy Item</h1>
            <p class="text-sm text-slate-500">Register new pharmaceuticals, disposables, surgical consumables, or consignment stock.</p>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-5">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-400">1. Classification & Identification</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Stock Classification *</label>
                    <select name="item_type" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="pharmacy" {{ old('item_type') == 'pharmacy' ? 'selected' : '' }}>💊 Pharmacy Stock (Medicines / IV Fluids)</option>
                        <option value="medical_supply" {{ old('item_type') == 'medical_supply' ? 'selected' : '' }}>💉 Medical Supply (Disposables / Consumables)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Category *</label>
                    <select name="category_id" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->type === 'pharmacy' ? 'Rx' : 'Supply' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Inventory Ownership Model *</label>
                    <select name="inventory_type" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="owned" {{ old('inventory_type') == 'owned' ? 'selected' : '' }}>Owned Stock (Hospital Purchased)</option>
                        <option value="consignment" {{ old('inventory_type') == 'consignment' ? 'selected' : '' }}>Consignment Stock (Pay Supplier Upon Usage)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Item Display Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Amoxicillin 500mg Capsule or 5ml Syringe w/ Needle" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Generic Name / Active Molecule</label>
                    <input type="text" name="generic_name" value="{{ old('generic_name') }}" placeholder="e.g. Amoxicillin Trihydrate or Polypropylene Syringe" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Brand Name / Manufacturer</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name') }}" placeholder="e.g. Himox, B. Braun, ApexCare, Roche" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dosage Form / Packaging Specification</label>
                    <input type="text" name="dosage_form" value="{{ old('dosage_form') }}" placeholder="e.g. 500mg Capsule, 100ml Syrup, Sterile Pouch" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Item Code / SKU *</label>
                    <input type="text" name="sku" value="{{ old('sku', 'MED-' . strtoupper(Str::random(6))) }}" required class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Barcode / UPC (Optional)</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}" placeholder="Scan or enter barcode" class="w-full text-xs font-mono rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Unit of Measure *</label>
                    <input type="text" name="unit_of_measure" value="{{ old('unit_of_measure', 'box') }}" required placeholder="e.g. box, vial, piece, bottle, ampule" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-5">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-400">2. Financials, Reorder Levels & Storage</h3>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Purchase / Cost Price (₱) *</label>
                    <input type="number" step="0.01" min="0" name="default_purchase_price" value="{{ old('default_purchase_price', '0.00') }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-right font-semibold focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Selling / Charge Price (₱) *</label>
                    <input type="number" step="0.01" min="0" name="selling_price" value="{{ old('selling_price', '0.00') }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-right font-bold text-sky-700 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Min Reorder Alert Level *</label>
                    <input type="number" min="0" name="min_stock_alert" value="{{ old('min_stock_alert', 15) }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-right focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Max Stock Capacity *</label>
                    <input type="number" min="1" name="max_stock_level" value="{{ old('max_stock_level', 300) }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 text-right focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Storage Condition *</label>
                    <select name="storage_condition" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="room_temperature" {{ old('storage_condition') == 'room_temperature' ? 'selected' : '' }}>Room Temperature (15°C - 25°C)</option>
                        <option value="refrigerated_2_8c" {{ old('storage_condition') == 'refrigerated_2_8c' ? 'selected' : '' }}>❄️ Cold Chain Refrigerated (2°C - 8°C)</option>
                        <option value="cold_storage" {{ old('storage_condition') == 'cold_storage' ? 'selected' : '' }}>Deep Freeze / Cold Storage (&lt;0°C)</option>
                        <option value="controlled_substance" {{ old('storage_condition') == 'controlled_substance' ? 'selected' : '' }}>🔒 Controlled Substance (Dangerous Drugs Board Vault)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Primary Supplier / Distributor</label>
                    <select name="supplier_id" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="">-- None / Multiple Suppliers --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Item Clinical Notes / Instructions</label>
                <textarea name="description" rows="2" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500" placeholder="Clinical administration notes, storage precautions, consignment details...">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-md shadow-sky-600/20 transition">
                ✓ Save Item
            </button>
        </div>

    </form>

</div>
@endsection
