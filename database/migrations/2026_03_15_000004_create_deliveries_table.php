<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique(); // e.g. DEL-2026-0001
            $table->string('invoice_number')->nullable(); // Supplier Delivery Receipt / Invoice
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('delivery_date');
            $table->string('received_by')->nullable(); // Name of receiving staff/pharmacist
            $table->enum('status', ['received', 'pending_inspection', 'partially_received', 'cancelled'])->default('received');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'consignment_term'])->default('unpaid');
            $table->decimal('amount_paid', 12, 2)->default(0.00);
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
