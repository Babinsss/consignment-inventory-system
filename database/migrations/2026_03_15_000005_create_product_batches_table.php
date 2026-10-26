<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_id')->nullable()->constrained()->nullOnDelete();
            $table->string('batch_number'); // Batch # / Lot #
            $table->string('lot_number')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date'); // Crucial for FEFO (First Expire First Out)
            $table->integer('quantity_received');
            $table->integer('quantity_remaining');
            $table->decimal('unit_purchase_cost', 12, 2)->default(0.00);
            $table->decimal('unit_selling_price', 12, 2)->default(0.00);
            $table->enum('status', ['active', 'depleted', 'expired', 'quarantined'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
