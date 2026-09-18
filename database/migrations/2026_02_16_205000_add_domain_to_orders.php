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
        // 1. Item Orders
        if (Schema::hasTable('item_orders') && !Schema::hasColumn('item_orders', 'domain')) {
            Schema::table('item_orders', function (Blueprint $table) {
                $table->string('domain')->nullable()->index()->after('user_id');
            });
        }

        // 2. GB Orders (Assuming table is gb_orders based on model name)
        if (Schema::hasTable('gb_orders') && !Schema::hasColumn('gb_orders', 'domain')) {
            Schema::table('gb_orders', function (Blueprint $table) {
                $table->string('domain')->nullable()->index()->after('user_id');
            });
        }

        // 3. Boosting Orders (Assuming table name, checking model next but safe to check schema)
        if (Schema::hasTable('boosting_orders') && !Schema::hasColumn('boosting_orders', 'domain')) {
            Schema::table('boosting_orders', function (Blueprint $table) {
                $table->string('domain')->nullable()->index()->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('item_orders')) {
            Schema::table('item_orders', function (Blueprint $table) {
                $table->dropColumn('domain');
            });
        }
        if (Schema::hasTable('gb_orders')) {
            Schema::table('gb_orders', function (Blueprint $table) {
                $table->dropColumn('domain');
            });
        }
        if (Schema::hasTable('boosting_orders')) {
            Schema::table('boosting_orders', function (Blueprint $table) {
                $table->dropColumn('domain');
            });
        }
    }
};
