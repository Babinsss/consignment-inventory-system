@extends('layouts.app')

@section('title', 'Process Dispensation / Issue Stock')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="salesCheckout()">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center mb-1">
                &larr; Back to Dispensing Ledger
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Dispense Medical Supplies & Pharmacy Stocks</h1>
            <p class="text-sm text-slate-500">Auto-deducts from earliest expiring batches via FEFO (First Expire, First Out).</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-400 block">Txn Number</span>
            <span class="text-sm font-mono font-bold text-slate-800">{{ $nextSaleNumber }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('sales.store') }}" @submit="validateAndSubmit($event)">
        @csrf

        <!-- Recipient & Transaction Header -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-400">1. Recipient & Prescription Info</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Ref # *</label>
                    <input type="text" name="sale_number" value="{{ old('sale_number', $nextSaleNumber) }}" required class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 px-3 py-2 bg-slate-50 text-slate-800 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Issue Type *</label>
                    <select name="transaction_type" x-model="transactionType" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="dispensing">Prescription Dispensing (Patient)</option>
                        <option value="department_issue">Hospital Department Stock Issue (ER/OR/ICU)</option>
                        <option value="walk_in_sale">Walk-in OTC Sale</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Date & Time *</label>
                    <input type="datetime-local" name="sale_date" value="{{ old('sale_date', date('Y-m-d\TH:i')) }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <!-- Conditional Patient Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4" x-show="transactionType === 'dispensing' || transactionType === 'walk_in_sale'">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Patient Full Name</label>
                    <input type="text" name="patient_name" value="{{ old('patient_name') }}" placeholder="e.g. Maria Santos" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Medical Record # (MRN)</label>
                    <input type="text" name="patient_mrn" value="{{ old('patient_mrn') }}" placeholder="e.g. MRN-2026-0042" class="w-full text-xs font-mono rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Attending Physician / Doctor</label>
                    <input type="text" name="doctor_name" value="{{ old('doctor_name') }}" placeholder="Dr. Name, MD" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prescription #</label>
                    <input type="text" name="prescription_no" value="{{ old('prescription_no') }}" placeholder="RX-2026-XXX" class="w-full text-xs font-mono rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <!-- Conditional Department Issue Field -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="transactionType === 'department_issue'">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Hospital Department *</label>
                    <select name="department_name" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="Emergency Room (ER)">Emergency Room (ER)</option>
                        <option value="Operating Room (OR)">Operating Room (OR) / Surgical Suite</option>
                        <option value="Intensive Care Unit (ICU)">Intensive Care Unit (ICU)</option>
                        <option value="Inpatient Ward 2">Inpatient Ward 2</option>
                        <option value="Inpatient Ward 3">Inpatient Ward 3</option>
                        <option value="Pediatrics Ward">Pediatrics Ward</option>
                        <option value="Hemodialysis Unit">Hemodialysis Unit</option>
                        <option value="Clinical Laboratory">Clinical Laboratory</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Requisition / Case Slip #</label>
                    <input type="text" name="patient_mrn" placeholder="e.g. REQ-ER-2026-110" class="w-full text-xs font-mono rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="cash">Cash</option>
                        <option value="credit_card">Credit / Debit Card</option>
                        <option value="philhealth">PhilHealth Health Insurance</option>
                        <option value="hmo_insurance">HMO / Private Health Insurance</option>
                        <option value="department_charge">Charge to Department Budget</option>
                        <option value="consignment_settled">Consignment Consumption Settled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Status *</label>
                    <select name="payment_status" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="paid">Paid in Full / Settled</option>
                        <option value="pending">Pending Settlement</option>
                        <option value="waived">Charity / Waived</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dispensed By (Pharmacist / Nurse) *</label>
                    <input type="text" name="dispensed_by" value="{{ old('dispensed_by', 'Pharm. Angela Cruz, RPh') }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>
            </div>
        </div>

        <!-- Line Items Table Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-400">2. Medications & Supplies to Issue</h3>
                    <p class="text-xs text-slate-500">Nearest-to-expire active batch will automatically be deducted upon submission</p>
                </div>
                <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold transition border border-emerald-200">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    + Add Product Line
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 min-w-[260px]">Product / Medicine</th>
                            <th class="py-2.5 px-3 min-w-[130px]">Available Stock</th>
                            <th class="py-2.5 px-3 min-w-[100px]">Qty to Issue</th>
                            <th class="py-2.5 px-3 min-w-[120px]">Unit Price (₱)</th>
                            <th class="py-2.5 px-3 min-w-[120px]">Line Total (₱)</th>
                            <th class="py-2.5 px-3 min-w-[110px]">Est. Profit</th>
                            <th class="py-2.5 px-2 text-center w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-slate-50/50">
                                <!-- Product Select -->
                                <td class="p-2">
                                    <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="onProductSelect(index)" required class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:ring-1 focus:ring-sky-500">
                                        <option value="">-- Choose Stock Item --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-cost="{{ $p->default_purchase_price }}" data-stock="{{ $p->total_stock }}" data-uom="{{ $p->unit_of_measure }}" data-sku="{{ $p->sku }}" data-exp="{{ $p->nearest_expiry ? date('M d, Y', strtotime($p->nearest_expiry)) : 'N/A' }}">
                                                [{{ $p->sku }}] {{ $p->name }} (Available: {{ $p->total_stock }} {{ $p->unit_of_measure }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="text-[10px] text-slate-400 mt-0.5" x-text="item.product_meta"></div>
                                </td>

                                <!-- Available Stock Badge -->
                                <td class="p-2 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 text-sm" x-text="item.stock_available"></span>
                                    <span class="text-[10px] text-slate-400" x-text="item.unit_of_measure"></span>
                                    <div class="text-[9px] text-amber-700" x-show="item.nearest_expiry">
                                        FEFO Exp: <span x-text="item.nearest_expiry"></span>
                                    </div>
                                </td>

                                <!-- Quantity -->
                                <td class="p-2">
                                    <input type="number" min="1" :max="item.stock_available || 9999" step="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="calculateLine(index)" required class="w-full text-xs rounded-lg border border-slate-300 p-2 text-right font-bold focus:ring-1 focus:ring-sky-500">
                                </td>

                                <!-- Unit Price -->
                                <td class="p-2">
                                    <input type="number" min="0" step="0.01" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" @input="calculateLine(index)" required class="w-full text-xs rounded-lg border border-slate-300 p-2 text-right focus:ring-1 focus:ring-sky-500 font-semibold">
                                </td>

                                <!-- Line Subtotal -->
                                <td class="p-2 text-right font-bold text-slate-900">
                                    ₱<span x-text="formatNumber(item.subtotal)"></span>
                                </td>

                                <!-- Estimated Profit -->
                                <td class="p-2 text-right text-emerald-700 font-semibold">
                                    ₱<span x-text="formatNumber(item.est_profit)"></span>
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

            <!-- Discount, Summary & Total Section -->
            <div class="pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Discount Amount (₱)</label>
                        <input type="number" min="0" step="0.01" name="discount_amount" x-model.number="discountAmount" placeholder="0.00" class="w-48 text-xs rounded-lg border border-slate-300 px-3 py-2 text-right font-semibold">
                        <span class="text-[10px] text-slate-400 block mt-0.5">Senior Citizen / PWD / Hospital Subsidy discount</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Dispensing Remarks</label>
                        <input type="text" name="notes" placeholder="Prescription notes, diagnosis, instructions..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <!-- Financial Calculation Box -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Gross Total:</span>
                        <span class="font-semibold text-slate-800">₱<span x-text="formatNumber(grossTotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Discount:</span>
                        <span class="text-rose-600 font-semibold">-₱<span x-text="formatNumber(discountAmount || 0)"></span></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Est. Cost of Goods (COGS):</span>
                        <span class="font-semibold text-slate-600">₱<span x-text="formatNumber(totalEstCost)"></span></span>
                    </div>
                    <div class="flex justify-between text-emerald-700 font-bold border-t border-slate-200 pt-1">
                        <span>Est. Gross Profit:</span>
                        <span>₱<span x-text="formatNumber(netProfit)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-slate-900 border-t border-slate-200 pt-2 text-base font-extrabold">
                        <span>Net Total Due:</span>
                        <span class="text-sky-700 text-xl">₱<span x-text="formatNumber(netTotal)"></span></span>
                    </div>

                    <button type="submit" class="w-full mt-3 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-600/20 transition">
                        ✓ Confirm & Issue Stock
                    </button>
                </div>
            </div>

        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    function salesCheckout() {
        return {
            transactionType: 'dispensing',
            discountAmount: 0,
            items: [
                {
                    product_id: '',
                    product_meta: '',
                    unit_of_measure: '',
                    stock_available: 0,
                    nearest_expiry: '',
                    unit_cost: 0,
                    unit_price: 0,
                    quantity: 1,
                    subtotal: 0,
                    est_profit: 0
                }
            ],
            addItem() {
                this.items.push({
                    product_id: '',
                    product_meta: '',
                    unit_of_measure: '',
                    stock_available: 0,
                    nearest_expiry: '',
                    unit_cost: 0,
                    unit_price: 0,
                    quantity: 1,
                    subtotal: 0,
                    est_profit: 0
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
                    item.unit_price = parseFloat(opt.dataset.price) || 0;
                    item.unit_cost = parseFloat(opt.dataset.cost) || 0;
                    item.stock_available = parseInt(opt.dataset.stock) || 0;
                    item.unit_of_measure = opt.dataset.uom || '';
                    item.nearest_expiry = opt.dataset.exp || '';
                    item.product_meta = `SKU: ${opt.dataset.sku}`;
                    this.calculateLine(index);
                }
            },
            calculateLine(index) {
                const item = this.items[index];
                const qty = item.quantity || 0;
                item.subtotal = qty * (item.unit_price || 0);
                item.est_profit = item.subtotal - (qty * (item.unit_cost || 0));
            },
            get grossTotal() {
                return this.items.reduce((acc, curr) => acc + (curr.subtotal || 0), 0);
            },
            get totalEstCost() {
                return this.items.reduce((acc, curr) => acc + ((curr.quantity || 0) * (curr.unit_cost || 0)), 0);
            },
            get netTotal() {
                return Math.max(0, this.grossTotal - (this.discountAmount || 0));
            },
            get netProfit() {
                return this.netTotal - this.totalEstCost;
            },
            formatNumber(num) {
                return Number(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            validateAndSubmit(e) {
                if (this.items.length === 0) {
                    alert('Please select at least one item to dispense.');
                    e.preventDefault();
                    return;
                }
                for (let i = 0; i < this.items.length; i++) {
                    const itm = this.items[i];
                    if (!itm.product_id) {
                        alert(`Please select an item for line #${i + 1}`);
                        e.preventDefault();
                        return;
                    }
                    if (itm.quantity > itm.stock_available) {
                        alert(`Insufficient stock for line #${i + 1}. Available: ${itm.stock_available}, Requested: ${itm.quantity}`);
                        e.preventDefault();
                        return;
                    }
                }
            }
        }
    }
</script>
@endpush
