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
        // Fix for GB Orders table name (g_b_orders)
        if (Schema::hasTable('g_b_orders') && !Schema::hasColumn('g_b_orders', 'domain')) {
            Schema::table('g_b_orders', function (Blueprint $table) {
                $table->string('domain')->nullable()->index()->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('g_b_orders')) {
            Schema::table('g_b_orders', function (Blueprint $table) {
                $table->dropColumn('domain');
            });
        }
    }
};
