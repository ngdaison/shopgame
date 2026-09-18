<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     * Adds domain column to payment-related tables that don't have it yet.
     */
    public function up(): void
    {
        $tables = ['usdt', 'paypal', 'perfect_money', 'banking', 'cards'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'domain')) {
                Schema::table($table, function (Blueprint $table_bp) {
                    $table_bp->string('domain')->nullable()->after('username');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['usdt', 'paypal', 'perfect_money', 'banking', 'cards'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'domain')) {
                Schema::table($table, function (Blueprint $table_bp) {
                    $table_bp->dropColumn('domain');
                });
            }
        }
    }
};
