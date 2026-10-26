@extends('layouts.app')

@section('title', 'Edit ' . $supplier->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('suppliers.show', $supplier) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center mb-1">
                &larr; Back to Supplier Profile
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Supplier: {{ $supplier->name }}</h1>
            <p class="text-sm text-slate-500">Update contact info, payment terms, and active status.</p>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Company / Supplier Name *</label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier Code *</label>
                <input type="text" name="code" value="{{ old('code', $supplier->code) }}" required class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tax / TIN #</label>
                <input type="text" name="tax_id" value="{{ old('tax_id', $supplier->tax_id) }}" class="w-full text-xs font-mono rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Terms *</label>
                <select name="payment_terms" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                    <option value="Net 30" {{ old('payment_terms', $supplier->payment_terms) == 'Net 30' ? 'selected' : '' }}>Net 30 Days</option>
                    <option value="Net 15" {{ old('payment_terms', $supplier->payment_terms) == 'Net 15' ? 'selected' : '' }}>Net 15 Days</option>
                    <option value="Net 60" {{ old('payment_terms', $supplier->payment_terms) == 'Net 60' ? 'selected' : '' }}>Net 60 Days</option>
                    <option value="COD" {{ old('payment_terms', $supplier->payment_terms) == 'COD' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                    <option value="Consignment" {{ old('payment_terms', $supplier->payment_terms) == 'Consignment' ? 'selected' : '' }}>Consignment Stock Agreement</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status *</label>
                <select name="status" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                    <option value="active" {{ old('status', $supplier->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $supplier->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Address</label>
            <input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Notes</label>
            <textarea name="notes" rows="2" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">{{ old('notes', $supplier->notes) }}</textarea>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
            <a href="{{ route('suppliers.show', $supplier) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-lg shadow-xs">✓ Update Supplier</button>
        </div>
    </form>

</div>
@endsection
