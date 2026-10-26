<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('item_type', ['medical_supply', 'pharmacy'])->default('pharmacy');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->index();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('dosage_form')->nullable(); // e.g., 500mg Capsule, 100ml Syrup, Sterile Pack
            $table->string('unit_of_measure')->default('piece'); // piece, box, vial, ampule, bottle, pack
            $table->enum('inventory_type', ['owned', 'consignment'])->default('owned');
            $table->integer('min_stock_alert')->default(10);
            $table->integer('max_stock_level')->default(500);
            $table->decimal('default_purchase_price', 12, 2)->default(0.00);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->enum('storage_condition', [
                'room_temperature',
                'refrigerated_2_8c',
                'cold_storage',
                'controlled_substance'
            ])->default('room_temperature');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
