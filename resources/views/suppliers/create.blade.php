@extends('layouts.app')

@section('title', 'Register Supplier')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('suppliers.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center mb-1">
                &larr; Back to Suppliers
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Register Supplier / Distributor</h1>
            <p class="text-sm text-slate-500">Add medical device companies, pharma manufacturers, or consignment vendors.</p>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('suppliers.store') }}" class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Company / Supplier Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Zuellig Pharma Corp." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier Code *</label>
                <input type="text" name="code" value="{{ old('code', 'SUP-' . strtoupper(Str::random(5))) }}" required class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person') }}" placeholder="Account Manager / Sales Rep" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+63 2 8888 8888" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="orders@supplier.com" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tax / TIN #</label>
                <input type="text" name="tax_id" value="{{ old('tax_id') }}" placeholder="000-000-000-000" class="w-full text-xs font-mono rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Default Payment Terms *</label>
                <select name="payment_terms" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                    <option value="Net 30">Net 30 Days</option>
                    <option value="Net 15">Net 15 Days</option>
                    <option value="Net 60">Net 60 Days</option>
                    <option value="COD">Cash on Delivery (COD)</option>
                    <option value="Consignment">Consignment Stock Agreement (Pay on Usage)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Office / Warehouse Address</label>
                <input type="text" name="address" value="{{ old('address') }}" placeholder="Street, City, Metro Manila" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier Notes / Consignment Terms</label>
            <textarea name="notes" rows="2" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500" placeholder="Product lines carried, consignment agreement details, bank account info...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
            <a href="{{ route('suppliers.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg shadow-xs">✓ Save Supplier</button>
        </div>
    </form>

</div>
@endsection
