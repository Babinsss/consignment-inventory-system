@extends('layouts.app')

@section('title', 'Goods Received Voucher - ' . $delivery->delivery_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ paymentModal: false }">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('deliveries.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800 flex items-center">
            &larr; Back to Deliveries List
        </a>
        <div class="flex items-center space-x-2">
            @if($delivery->balance_due > 0)
            <button type="button" @click="paymentModal = true" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                + Record Payment
            </button>
            @endif
            <button type="button" onclick="window.print()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-300">
                🖨️ Print Voucher
            </button>
        </div>
    </div>

    <!-- Official Receiving Document / Voucher Card -->
    <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-xs space-y-6">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-200 pb-6 gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-1 bg-sky-100 text-sky-800 rounded-lg text-xs font-bold uppercase tracking-wider">Goods Received Note (GRN)</span>
                    @if($delivery->payment_status === 'paid')
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold uppercase">PAID IN FULL</span>
                    @elseif($delivery->payment_status === 'partially_paid')
                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg text-xs font-bold uppercase">PARTIALLY PAID</span>
                    @elseif($delivery->payment_status === 'consignment_term')
                        <span class="px-2.5 py-1 bg-purple-100 text-purple-800 rounded-lg text-xs font-bold uppercase">CONSIGNMENT AGREEMENT</span>
                    @else
                        <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-lg text-xs font-bold uppercase">UNPAID</span>
                    @endif
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 mt-2 tracking-tight">{{ $delivery->delivery_number }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">Supplier Delivery Receipt / Invoice: <strong class="text-slate-700">{{ $delivery->invoice_number ?: 'N/A' }}</strong></p>
            </div>

            <div class="text-left sm:text-right text-xs text-slate-600 space-y-1">
                <div>Delivery Date: <strong class="text-slate-900">{{ $delivery->delivery_date->format('F d, Y') }}</strong></div>
                <div>Received By: <strong class="text-slate-900">{{ $delivery->received_by }}</strong></div>
                <div>Status: <span class="capitalize font-semibold text-emerald-700">{{ str_replace('_', ' ', $delivery->status) }}</span></div>
            </div>
        </div>

        <!-- Supplier & Receiving Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-100 text-xs">
            <div>
                <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Supplier Details</span>
                <h3 class="text-sm font-bold text-slate-900">{{ $delivery->supplier->name }}</h3>
                <p class="text-slate-600">Contact: {{ $delivery->supplier->contact_person ?? 'N/A' }} ({{ $delivery->supplier->phone ?? 'No phone' }})</p>
                <p class="text-slate-600">Email: {{ $delivery->supplier->email ?? 'N/A' }}</p>
                <p class="text-slate-600">Address: {{ $delivery->supplier->address ?? 'N/A' }}</p>
                <p class="text-slate-600">Payment Terms: <span class="font-semibold text-slate-800">{{ $delivery->supplier->payment_terms }}</span></p>
            </div>

            <div class="sm:text-right">
                <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Financial Summary</span>
                <div class="text-sm">Total Invoiced: <strong class="text-slate-900 font-bold">₱{{ number_format($delivery->total_amount, 2) }}</strong></div>
                <div class="text-sm text-emerald-700">Amount Paid: <strong>₱{{ number_format($delivery->amount_paid, 2) }}</strong></div>
                <div class="text-base font-extrabold {{ $delivery->balance_due > 0 ? 'text-rose-600' : 'text-slate-700' }} mt-1">
                    Balance Due: ₱{{ number_format($delivery->balance_due, 2) }}
                </div>
                @if($delivery->due_date)
                    <p class="text-[11px] text-slate-500 mt-1">Due Date: {{ $delivery->due_date->format('M d, Y') }}</p>
                @endif
            </div>
        </div>

        @if($delivery->notes)
        <div class="text-xs bg-sky-50/60 p-3 rounded-lg border border-sky-100 text-sky-900">
            <strong>Notes / Quality Inspection:</strong> {{ $delivery->notes }}
        </div>
        @endif

        <!-- Itemized Table -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Received Stocks & Batch Allocation</h3>
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Item / SKU</th>
                            <th class="py-2.5 px-3">Category</th>
                            <th class="py-2.5 px-3">Batch / Lot #</th>
                            <th class="py-2.5 px-3">Expiry Date</th>
                            <th class="py-2.5 px-3 text-right">Qty Received</th>
                            <th class="py-2.5 px-3 text-right">Unit Cost</th>
                            <th class="py-2.5 px-3 text-right">Selling Price</th>
                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($delivery->items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 px-3">
                                <a href="{{ route('products.show', $item->product) }}" class="font-bold text-slate-900 hover:text-sky-600 hover:underline">
                                    {{ $item->product->name }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $item->product->sku }}</span>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $item->product->item_type === 'pharmacy' ? 'bg-sky-100 text-sky-800' : 'bg-teal-100 text-teal-800' }}">
                                    {{ $item->product->item_type === 'pharmacy' ? 'Pharmacy' : 'Medical Supply' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-mono font-semibold text-slate-800">
                                {{ $item->batch_number }}
                            </td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="font-medium text-slate-800">{{ $item->expiry_date->format('M d, Y') }}</span>
                                @if($item->batch)
                                    <span class="block text-[10px] text-slate-400">({{ $item->batch->days_until_expiry }} days left)</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-semibold text-slate-900">
                                {{ $item->quantity }} <span class="text-[10px] text-slate-400 font-normal">{{ $item->product->unit_of_measure }}</span>
                            </td>
                            <td class="py-3 px-3 text-right text-slate-700">
                                ₱{{ number_format($item->unit_cost, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right text-slate-700">
                                ₱{{ number_format($item->selling_price, 2) }}
                            </td>
                            <td class="py-3 px-3 text-right font-bold text-slate-900">
                                ₱{{ number_format($item->subtotal, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 border-t border-slate-200 font-bold text-slate-900">
                        <tr>
                            <td colspan="7" class="py-3 px-3 text-right uppercase text-[11px]">Voucher Total Amount:</td>
                            <td class="py-3 px-3 text-right text-sm text-sky-700">₱{{ number_format($delivery->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Supplier Payment Ledger for this Delivery -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Payment Transactions Against This Voucher</h3>
                @if($delivery->balance_due > 0)
                <button type="button" @click="paymentModal = true" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 no-print">
                    + Add Payment
                </button>
                @endif
            </div>

            @if($delivery->payments->count() > 0)
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Payment #</th>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">Method</th>
                            <th class="py-2.5 px-3">Ref / Check #</th>
                            <th class="py-2.5 px-3">Recorded By</th>
                            <th class="py-2.5 px-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($delivery->payments as $p)
                        <tr>
                            <td class="py-2.5 px-3 font-mono font-semibold text-slate-800">{{ $p->payment_number }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $p->payment_date->format('M d, Y') }}</td>
                            <td class="py-2.5 px-3 capitalize text-slate-700">{{ str_replace('_', ' ', $p->payment_method) }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $p->reference_number ?: '—' }}</td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $p->recorded_by }}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-emerald-700">₱{{ number_format($p->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-xs text-slate-400 italic">No payments recorded against this delivery yet.</p>
            @endif
        </div>

        <!-- Signature Section for Print -->
        <div class="pt-8 border-t border-slate-200 grid grid-cols-3 gap-6 text-xs text-center">
            <div>
                <div class="border-b border-slate-300 pb-1 mb-1 font-semibold text-slate-800">{{ $delivery->received_by }}</div>
                <div class="text-slate-400 text-[10px]">Received & Inspected By</div>
            </div>
            <div>
                <div class="border-b border-slate-300 pb-1 mb-1 font-semibold text-slate-800">&nbsp;</div>
                <div class="text-slate-400 text-[10px]">Chief Pharmacist / Inventory Custodian</div>
            </div>
            <div>
                <div class="border-b border-slate-300 pb-1 mb-1 font-semibold text-slate-800">&nbsp;</div>
                <div class="text-slate-400 text-[10px]">Finance / Accounts Payable Officer</div>
            </div>
        </div>

    </div>

    <!-- Record Payment Modal -->
    <div x-show="paymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="paymentModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Record Payment to {{ $delivery->supplier->name }}</h3>
                <button type="button" @click="paymentModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form method="POST" action="{{ route('deliveries.payment', $delivery) }}" class="space-y-4">
                @csrf
                <div class="bg-slate-50 p-3 rounded-lg text-xs space-y-1">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Voucher:</span>
                        <strong class="text-slate-800">₱{{ number_format($delivery->total_amount, 2) }}</strong>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Amount Already Paid:</span>
                        <strong class="text-emerald-700">₱{{ number_format($delivery->amount_paid, 2) }}</strong>
                    </div>
                    <div class="flex justify-between text-slate-900 font-bold border-t border-slate-200 pt-1">
                        <span>Outstanding Balance Due:</span>
                        <span class="text-rose-600">₱{{ number_format($delivery->balance_due, 2) }}</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Amount (₱) *</label>
                    <input type="number" step="0.01" min="0.01" max="{{ $delivery->balance_due }}" name="amount" value="{{ $delivery->balance_due }}" required class="w-full text-sm font-bold rounded-lg border border-slate-300 px-3 py-2 text-right focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Date *</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                        <option value="bank_transfer">Bank Transfer / Online Wire</option>
                        <option value="cheque">Company Cheque</option>
                        <option value="cash">Petty Cash / COD</option>
                        <option value="electronic">Electronic / Card</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reference / Cheque / Transaction #</label>
                    <input type="text" name="reference_number" placeholder="e.g. BDO-TRX-19412 or CHK-4421" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Recorded By *</label>
                    <input type="text" name="recorded_by" value="Finance Officer" required class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-sky-500" placeholder="Optional notes..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-2">
                    <button type="button" @click="paymentModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-xs">✓ Post Payment</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
