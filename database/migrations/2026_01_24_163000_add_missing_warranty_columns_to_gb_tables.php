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
        if (Schema::hasTable('g_b_packages') && !Schema::hasColumn('g_b_packages', 'warranty_hours')) {
            Schema::table('g_b_packages', function (Blueprint $table) {
                $table->integer('warranty_hours')->nullable()->default(0);
            });
        }

        if (Schema::hasTable('g_b_orders') && !Schema::hasColumn('g_b_orders', 'warranty_expire_at')) {
            Schema::table('g_b_orders', function (Blueprint $table) {
                $table->timestamp('warranty_expire_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('g_b_packages') && Schema::hasColumn('g_b_packages', 'warranty_hours')) {
            Schema::table('g_b_packages', function (Blueprint $table) {
                $table->dropColumn('warranty_hours');
            });
        }

        if (Schema::hasTable('g_b_orders') && Schema::hasColumn('g_b_orders', 'warranty_expire_at')) {
            Schema::table('g_b_orders', function (Blueprint $table) {
                $table->dropColumn('warranty_expire_at');
            });
        }
    }
};
