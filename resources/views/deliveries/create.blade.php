@extends('layouts.app')

@section('title', 'Receive New Delivery')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="deliveryForm()">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('deliveries.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center mb-1">
                &larr; Back to Deliveries
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Receive New Delivery & Stocks</h1>
            <p class="text-sm text-slate-500">Record incoming medical supplies and pharmacy stocks with batch numbers, expiry dates, and costs.</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-400 block">System Delivery Ref</span>
            <span class="text-sm font-mono font-bold text-slate-800">{{ $nextDeliveryNumber }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('deliveries.store') }}" @submit="validateAndSubmit($event)">
        @csrf

        <!-- Top Receiving Header Form Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-400">1. Delivery Voucher Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Ref # *</label>
                    <input type="text" name="delivery_number" value="{{ old('delivery_number', $nextDeliveryNumber) }}" required class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 px-3 py-2 bg-slate-50 text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier / Vendor *</label>
                    <select name="supplier_id" x-model="selectedSupplierId" @change="onSupplierChange()" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="">-- Select Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-terms="{{ $s->payment_terms }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->payment_terms }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Supplier DR / Invoice #</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="e.g. INV-88910 or DR-4412" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Delivery Date *</label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', date('Y-m-d')) }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Received By (Staff / Pharmacist) *</label>
                    <input type="text" name="received_by" value="{{ old('received_by', 'Chief Pharmacist') }}" required placeholder="Staff Name" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Terms / Status *</label>
                    <select name="payment_status" x-model="paymentStatus" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="unpaid">Unpaid (Accounts Payable)</option>
                        <option value="partially_paid">Partially Paid</option>
                        <option value="paid">Paid in Full (COD / Immediate)</option>
                        <option value="consignment_term">Consignment Agreement (Pay on Usage)</option>
                    </select>
                </div>

                <div x-show="paymentStatus === 'partially_paid'">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Initial Amount Paid (₱)</label>
                    <input type="number" step="0.01" min="0" name="amount_paid" x-model="initialAmountPaid" placeholder="0.00" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Invoice Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Receiving Remarks / Storage Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Cold chain checked, packaging verified intact, seal undamaged..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>
        </div>

        <!-- Line Items Table Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-400">2. Delivered Items & Batches</h3>
                    <p class="text-xs text-slate-500">Assign batch numbers and expiry dates for FEFO compliance</p>
                </div>
                <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg text-xs font-semibold transition border border-sky-200">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    + Add Another Item
                </button>
            </div>

            <!-- Items Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 min-w-[200px]">Product / Supply</th>
                            <th class="py-2.5 px-3 min-w-[120px]">Batch / Lot #</th>
                            <th class="py-2.5 px-3 min-w-[130px]">Expiry Date *</th>
                            <th class="py-2.5 px-3 min-w-[100px]">Quantity</th>
                            <th class="py-2.5 px-3 min-w-[110px]">Unit Cost (₱)</th>
                            <th class="py-2.5 px-3 min-w-[110px]">Selling Price (₱)</th>
                            <th class="py-2.5 px-3 min-w-[120px]">Subtotal (₱)</th>
                            <th class="py-2.5 px-2 text-center w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-50/50">
                                <!-- Product Select -->
                                <td class="p-2">
                                    <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductSelect(index)" required class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:ring-1 focus:ring-sky-500">
                                        <option value="">-- Choose Product --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-cost="{{ $p->default_purchase_price }}" data-sell="{{ $p->selling_price }}" data-uom="{{ $p->unit_of_measure }}" data-sku="{{ $p->sku }}" data-type="{{ $p->item_type }}">
                                                [{{ $p->sku }}] {{ $p->name }} ({{ $p->unit_of_measure }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="text-[10px] text-slate-400 mt-0.5" x-text="item.product_meta"></div>
                                </td>

                                <!-- Batch # -->
                                <td class="p-2">
                                    <input type="text" :name="`items[${index}][batch_number]`" x-model="item.batch_number" required placeholder="e.g. LOT-26A" class="w-full text-xs font-mono rounded-lg border border-slate-300 p-2 focus:ring-1 focus:ring-sky-500">
                                </td>

                                <!-- Expiry Date -->
                                <td class="p-2">
                                    <input type="date" :name="`items[${index}][expiry_date]`" x-model="item.expiry_date" required class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:ring-1 focus:ring-sky-500">
                                </td>

                                <!-- Quantity -->
                                <td class="p-2">
                                    <input type="number" min="1" step="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="calculateSubtotal(index)" required class="w-full text-xs rounded-lg border border-slate-300 p-2 text-right font-semibold focus:ring-1 focus:ring-sky-500">
                                </td>

                                <!-- Unit Cost -->
                                <td class="p-2">
                                    <input type="number" min="0" step="0.01" :name="`items[${index}][unit_cost]`" x-model.number="item.unit_cost" @input="calculateSubtotal(index)" required class="w-full text-xs rounded-lg border border-slate-300 p-2 text-right focus:ring-1 focus:ring-sky-500">
                                </td>

                                <!-- Selling Price -->
                                <td class="p-2">
                                    <input type="number" min="0" step="0.01" :name="`items[${index}][selling_price]`" x-model.number="item.selling_price" class="w-full text-xs rounded-lg border border-slate-300 p-2 text-right focus:ring-1 focus:ring-sky-500">
                                </td>

                                <!-- Subtotal -->
                                <td class="p-2 text-right font-bold text-slate-800">
                                    ₱<span x-text="formatNumber(item.subtotal)"></span>
                                </td>

                                <!-- Remove -->
                                <td class="p-2 text-center">
                                    <button type="button" @click="removeItem(index)" class="text-rose-500 hover:text-rose-700 p-1" :disabled="items.length <= 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Grand Totals & Confirmation Summary -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Total Delivered Line Items: <strong class="text-slate-800" x-text="items.length"></strong>
                </div>

                <div class="flex items-center space-x-6 text-right">
                    <div>
                        <span class="text-xs text-slate-500 block uppercase">Grand Total (Voucher Cost)</span>
                        <span class="text-2xl font-extrabold text-slate-900">₱<span x-text="formatNumber(grandTotal)"></span></span>
                    </div>

                    <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-bold shadow-md shadow-sky-600/20 transition">
                        ✓ Confirm & Receive Delivery
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    function deliveryForm() {
        return {
            selectedSupplierId: '{{ old('supplier_id', '') }}',
            paymentStatus: '{{ old('payment_status', 'unpaid') }}',
            initialAmountPaid: 0,
            items: [
                {
                    product_id: '',
                    product_meta: '',
                    batch_number: 'LOT-' + new Date().getFullYear() + '-' + Math.floor(100 + Math.random() * 900),
                    expiry_date: '',
                    quantity: 1,
                    unit_cost: 0,
                    selling_price: 0,
                    subtotal: 0
                }
            ],
            init() {
                // Set default expiry date to 1 year ahead
                const nextYear = new Date();
                nextYear.setFullYear(nextYear.getFullYear() + 1);
                this.items[0].expiry_date = nextYear.toISOString().split('T')[0];
            },
            onSupplierChange() {
                const select = document.querySelector('select[name="supplier_id"]');
                const opt = select.options[select.selectedIndex];
                if (opt && opt.dataset.terms === 'Consignment') {
                    this.paymentStatus = 'consignment_term';
                }
            },
            addItem() {
                const nextYear = new Date();
                nextYear.setFullYear(nextYear.getFullYear() + 1);
                this.items.push({
                    product_id: '',
                    product_meta: '',
                    batch_number: 'LOT-' + new Date().getFullYear() + '-' + Math.floor(100 + Math.random() * 900),
                    expiry_date: nextYear.toISOString().split('T')[0],
                    quantity: 1,
                    unit_cost: 0,
                    selling_price: 0,
                    subtotal: 0
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            onProductSelect(index) {
                const item = this.items[index];
                const select = document.querySelectorAll('select[name*="[product_id]"]')[index];
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    item.unit_cost = parseFloat(opt.dataset.cost) || 0;
                    item.selling_price = parseFloat(opt.dataset.sell) || 0;
                    item.product_meta = `SKU: ${opt.dataset.sku} • Category: ${opt.dataset.type === 'pharmacy' ? 'Pharmacy' : 'Medical Supply'}`;
                    this.calculateSubtotal(index);
                }
            },
            calculateSubtotal(index) {
                const item = this.items[index];
                item.subtotal = (item.quantity || 0) * (item.unit_cost || 0);
            },
            get grandTotal() {
                return this.items.reduce((acc, curr) => acc + (curr.subtotal || 0), 0);
            },
            formatNumber(num) {
                return Number(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            validateAndSubmit(e) {
                if (this.items.length === 0) {
                    alert('Please add at least one line item.');
                    e.preventDefault();
                    return;
                }
                for (let i = 0; i < this.items.length; i++) {
                    if (!this.items[i].product_id) {
                        alert(`Please select a product for line #${i + 1}`);
                        e.preventDefault();
                        return;
                    }
                    if (!this.items[i].batch_number) {
                        alert(`Please enter a batch number for line #${i + 1}`);
                        e.preventDefault();
                        return;
                    }
                    if (!this.items[i].expiry_date) {
                        alert(`Please enter an expiry date for line #${i + 1}`);
                        e.preventDefault();
                        return;
                    }
                }
            }
        }
    }
</script>
@endpush
