<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('or_number')->nullable()->after('prescription_no'); // Official Receipt #
            $table->string('charge_slip_no')->nullable()->after('or_number'); // Charge Slip # (e.g. B24-000033)
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->integer('qty_returned')->default(0)->after('quantity');
            $table->integer('actual_qty_issued')->virtualAs('quantity - qty_returned')->after('qty_returned');
        });

        Schema::table('delivery_items', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['or_number', 'charge_slip_no']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['qty_returned', 'actual_qty_issued']);
        });

        Schema::table('delivery_items', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
