<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique(); // e.g. DISP-2026-0001
            $table->enum('transaction_type', ['dispensing', 'department_issue', 'walk_in_sale'])->default('dispensing');
            $table->string('patient_name')->nullable();
            $table->string('patient_mrn')->nullable(); // Medical Record Number
            $table->string('department_name')->nullable(); // ER, ICU, Ward 3, etc.
            $table->string('doctor_name')->nullable();
            $table->string('prescription_no')->nullable();
            $table->dateTime('sale_date');
            $table->decimal('subtotal_cost', 12, 2)->default(0.00); // Total Cost of Goods Sold
            $table->decimal('subtotal_amount', 12, 2)->default(0.00); // Gross Sales
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00); // Net Revenue
            $table->decimal('total_profit', 12, 2)->default(0.00); // Net Profit = total_amount - subtotal_cost
            $table->enum('payment_method', [
                'cash',
                'credit_card',
                'hmo_insurance',
                'philhealth',
                'department_charge',
                'consignment_settled'
            ])->default('cash');
            $table->enum('payment_status', ['paid', 'pending', 'waived'])->default('paid');
            $table->text('notes')->nullable();
            $table->string('dispensed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
