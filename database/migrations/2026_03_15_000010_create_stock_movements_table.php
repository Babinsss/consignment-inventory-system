<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->enum('movement_type', [
                'delivery_in',
                'sale_out',
                'dispense_out',
                'adjustment_add',
                'adjustment_deduct',
                'disposal_expired',
                'return_supplier'
            ]);
            $table->integer('quantity'); // Positive for inward, negative for outward
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->nullableMorphs('reference'); // delivery, sale, adjustment
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->text('reason')->nullable();
            $table->string('user_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
