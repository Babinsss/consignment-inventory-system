@extends('layouts.app')

@section('title', 'Receive New Delivery')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="deliveryForm()">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('deliveries.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center mb-1">
                &larr; Back to Deliveries
            </a>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Receive New Delivery &amp; Stocks</h1>
            <p class="text-xs sm:text-sm text-slate-500">Record incoming medical supplies and pharmacy stocks with batch numbers, expiry dates, and costs.</p>
        </div>
        <div class="text-left sm:text-right bg-white sm:bg-transparent p-3 sm:p-0 rounded-xl border sm:border-0 border-slate-200">
            <span class="text-xs text-slate-400 block font-medium">System Delivery Ref</span>
            <span class="text-sm font-mono font-bold text-sky-700">{{ $nextDeliveryNumber }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('deliveries.store') }}" @submit="validateAndSubmit($event)">
        @csrf

        <!-- Top Receiving Header Form Card -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">1. Delivery Voucher Details</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Delivery Ref # *</label>
                    <input type="text" name="delivery_number" value="{{ old('delivery_number', $nextDeliveryNumber) }}" required class="w-full text-xs font-mono font-bold rounded-xl border border-slate-300 px-3 py-2.5 bg-slate-50 text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Supplier / Consignor *</label>
                    <select name="supplier_id" x-model="selectedSupplierId" @change="onSupplierChange()" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="">-- Select Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" data-terms="{{ $s->payment_terms }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->payment_terms }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Supplier DR / Invoice #</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="e.g. INV-88910 or DR-4412" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Delivery Date *</label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', date('Y-m-d')) }}" required class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Received By (Staff / Pharmacist) *</label>
                    <input type="text" name="received_by" value="{{ old('received_by', auth()->user()?->name ?? 'Consignment Custodian') }}" required placeholder="Staff Name" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Terms / Status *</label>
                    <select name="payment_status" x-model="paymentStatus" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="unpaid">Unpaid (Accounts Payable)</option>
                        <option value="partially_paid">Partially Paid</option>
                        <option value="paid">Paid in Full (COD / Immediate)</option>
                        <option value="consignment_term">Consignment Agreement (Pay on Usage)</option>
                    </select>
                </div>

                <div x-show="paymentStatus === 'partially_paid'" x-cloak>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Initial Amount Paid (&#8369;)</label>
                    <input type="number" step="0.01" min="0" name="amount_paid" x-model="initialAmountPaid" placeholder="0.00" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Invoice Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Receiving Remarks / Storage Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Cold chain checked, packaging intact, seal undamaged..." class="w-full text-xs rounded-xl border border-slate-300 px-3 py-2.5 focus:ring-2 focus:ring-sky-500 focus:outline-none">
            </div>
        </div>

        <!-- Line Items Section Card -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">2. Delivered Items &amp; Batches</h3>
                    <p class="text-xs text-slate-500">Assign batch numbers and expiry dates for FEFO compliance</p>
                </div>
                <button type="button" @click="addItem()" class="inline-flex items-center justify-center px-4 py-2 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-xl text-xs font-bold transition border border-sky-200 self-start sm:self-auto">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Another Item</span>
                </button>
            </div>

            <!-- DESKTOP / TABLET DATA TABLE -->
            <div class="hidden md:block overflow-x-auto custom-scroll border border-slate-200 rounded-2xl">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3 min-w-[220px]">Product / Supply</th>
                            <th class="py-3 px-3 min-w-[130px]">Batch / Lot #</th>
                            <th class="py-3 px-3 min-w-[130px]">Expiry Date *</th>
                            <th class="py-3 px-3 min-w-[100px]">Quantity</th>
                            <th class="py-3 px-3 min-w-[110px]">Unit Cost (&#8369;)</th>
                            <th class="py-3 px-3 min-w-[110px]">Selling Price (&#8369;)</th>
                            <th class="py-3 px-3 min-w-[120px]">Subtotal (&#8369;)</th>
                            <th class="py-3 px-2 text-center w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-50/70 transition">
                                <!-- Product Select -->
                                <td class="p-2.5">
                                    <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductSelect(index)" required class="w-full text-xs rounded-xl border border-slate-300 p-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                        <option value="">-- Choose Product --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-cost="{{ $p->default_purchase_price }}" data-sell="{{ $p->selling_price }}" data-uom="{{ $p->unit_of_measure }}" data-sku="{{ $p->sku }}" data-type="{{ $p->item_type }}">
                                                [{{ $p->sku }}] {{ $p->name }} ({{ $p->unit_of_measure }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="text-[10px] text-slate-400 mt-1" x-text="item.product_meta"></div>
                                </td>

                                <!-- Batch # -->
                                <td class="p-2.5">
                                    <input type="text" :name="`items[${index}][batch_number]`" x-model="item.batch_number" required placeholder="LOT-2026-X" class="w-full text-xs font-mono rounded-xl border border-slate-300 p-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </td>

                                <!-- Expiry Date -->
                                <td class="p-2.5">
                                    <input type="date" :name="`items[${index}][expiry_date]`" x-model="item.expiry_date" required class="w-full text-xs rounded-xl border border-slate-300 p-2 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </td>

                                <!-- Quantity -->
                                <td class="p-2.5">
                                    <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="calculateSubtotal(index)" required class="w-full text-xs rounded-xl border border-slate-300 p-2 font-bold text-center focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </td>

                                <!-- Unit Cost -->
                                <td class="p-2.5">
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`" x-model.number="item.unit_cost" @input="calculateSubtotal(index)" required class="w-full text-xs rounded-xl border border-slate-300 p-2 font-mono text-right focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </td>

                                <!-- Selling Price -->
                                <td class="p-2.5">
                                    <input type="number" step="0.01" min="0" :name="`items[${index}][selling_price]`" x-model.number="item.selling_price" class="w-full text-xs rounded-xl border border-slate-300 p-2 font-mono text-right focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </td>

                                <!-- Subtotal -->
                                <td class="p-2.5 text-right font-mono font-bold text-slate-900">
                                    &#8369;<span x-text="formatNumber(item.subtotal)"></span>
                                </td>

                                <!-- Remove Action -->
                                <td class="p-2.5 text-center">
                                    <button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="text-slate-400 hover:text-rose-600 disabled:opacity-30 disabled:cursor-not-allowed p-1.5 rounded-lg hover:bg-rose-50 transition" title="Remove Item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE RESPONSIVE ITEM CARDS (< 768px) -->
            <div class="block md:hidden space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3 relative">
                        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                            <span class="text-xs font-bold text-sky-700">Item #<span x-text="index + 1"></span></span>
                            <button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="text-rose-500 hover:text-rose-700 disabled:opacity-30 text-xs font-semibold flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Remove</span>
                            </button>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Product *</label>
                            <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductSelect(index)" required class="w-full text-xs rounded-xl border border-slate-300 p-2.5 bg-white focus:ring-2 focus:ring-sky-500">
                                <option value="">-- Choose Product --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-cost="{{ $p->default_purchase_price }}" data-sell="{{ $p->selling_price }}" data-uom="{{ $p->unit_of_measure }}" data-sku="{{ $p->sku }}" data-type="{{ $p->item_type }}">
                                        [{{ $p->sku }}] {{ $p->name }} ({{ $p->unit_of_measure }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1" x-text="item.product_meta"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-1">Batch / Lot # *</label>
                                <input type="text" :name="`items[${index}][batch_number]`" x-model="item.batch_number" required placeholder="LOT-1234" class="w-full text-xs font-mono rounded-xl border border-slate-300 p-2 bg-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-1">Expiry Date *</label>
                                <input type="date" :name="`items[${index}][expiry_date]`" x-model="item.expiry_date" required class="w-full text-xs rounded-xl border border-slate-300 p-2 bg-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-1">Qty *</label>
                                <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="calculateSubtotal(index)" required class="w-full text-xs rounded-xl border border-slate-300 p-2 text-center bg-white font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-1">Cost (&#8369;) *</label>
                                <input type="number" step="0.01" min="0" :name="`items[${index}][unit_cost]`" x-model.number="item.unit_cost" @input="calculateSubtotal(index)" required class="w-full text-xs rounded-xl border border-slate-300 p-2 font-mono bg-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 mb-1">Sell (&#8369;)</label>
                                <input type="number" step="0.01" min="0" :name="`items[${index}][selling_price]`" x-model.number="item.selling_price" class="w-full text-xs rounded-xl border border-slate-300 p-2 font-mono bg-white">
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">Line Subtotal:</span>
                            <span class="font-bold text-slate-900 font-mono text-sm">&#8369;<span x-text="formatNumber(item.subtotal)"></span></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Grand Totals & Confirmation Summary -->
            <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Total Delivered Line Items: <strong class="text-slate-800" x-text="items.length"></strong>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 text-right">
                    <div>
                        <span class="text-[10px] text-slate-500 block uppercase font-bold">Grand Total (Voucher Cost)</span>
                        <span class="text-2xl font-extrabold text-slate-900">&#8369;<span x-text="formatNumber(grandTotal)"></span></span>
                    </div>

                    <button type="submit" class="px-6 py-3 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-md shadow-sky-600/20 transition active:scale-98">
                        &check; Confirm &amp; Receive Delivery
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
                const selects = document.querySelectorAll(`select[name="items[${index}][product_id]"]`);
                const select = selects[0] || selects[1];
                if (!select) return;

                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    item.unit_cost = parseFloat(opt.dataset.cost) || 0;
                    item.selling_price = parseFloat(opt.dataset.sell) || 0;
                    item.product_meta = `SKU: ${opt.dataset.sku} • Unit: ${opt.dataset.uom}`;
                    this.calculateSubtotal(index);
                }
            },
            calculateSubtotal(index) {
                const item = this.items[index];
                item.subtotal = (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0);
            },
            get grandTotal() {
                return this.items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0);
            },
            formatNumber(val) {
                return (parseFloat(val) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            validateAndSubmit(e) {
                for (let i = 0; i < this.items.length; i++) {
                    if (!this.items[i].product_id) {
                        alert(`Please select a product for line item #${i + 1}`);
                        e.preventDefault();
                        return;
                    }
                }
            }
        };
    }
</script>
@endpush
