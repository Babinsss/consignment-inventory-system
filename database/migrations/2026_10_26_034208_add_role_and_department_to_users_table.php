<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 40)->default('medical_supply_staff')->after('password');
            $table->string('department', 100)->nullable()->after('role');
            $table->string('title', 100)->nullable()->after('department');
            $table->boolean('is_active')->default(true)->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'department', 'title', 'is_active']);
        });
    }
};
