<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_requests', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id', 60)->index();
            $table->string('department_name', 100)->index();
            $table->string('requested_by', 120)->index();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('quantity_issued')->default(0);
            $table->string('purpose', 255)->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->enum('status', ['Pending', 'Approved', 'Denied'])->default('Pending')->index();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_requests');
    }
};
