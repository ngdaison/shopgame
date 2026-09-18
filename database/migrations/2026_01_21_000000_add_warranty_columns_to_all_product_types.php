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
        if (Schema::hasTable('list_item_v2_s') && !Schema::hasColumn('list_item_v2_s', 'warranty_hours')) {
            Schema::table('list_item_v2_s', function (Blueprint $table) {
                $table->integer('warranty_hours')->nullable()->default(0);
            });
        }

        if (Schema::hasTable('gb_packages') && !Schema::hasColumn('gb_packages', 'warranty_hours')) {
            Schema::table('gb_packages', function (Blueprint $table) {
                $table->integer('warranty_hours')->nullable()->default(0);
            });
        }

        if (Schema::hasTable('item_orders') && !Schema::hasColumn('item_orders', 'warranty_expire_at')) {
            Schema::table('item_orders', function (Blueprint $table) {
                $table->timestamp('warranty_expire_at')->nullable();
            });
        }

        if (Schema::hasTable('gb_orders') && !Schema::hasColumn('gb_orders', 'warranty_expire_at')) {
            Schema::table('gb_orders', function (Blueprint $table) {
                $table->timestamp('warranty_expire_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('list_item_v2_s') && Schema::hasColumn('list_item_v2_s', 'warranty_hours')) {
            Schema::table('list_item_v2_s', function (Blueprint $table) {
                $table->dropColumn('warranty_hours');
            });
        }

        if (Schema::hasTable('gb_packages') && Schema::hasColumn('gb_packages', 'warranty_hours')) {
            Schema::table('gb_packages', function (Blueprint $table) {
                $table->dropColumn('warranty_hours');
            });
        }

        if (Schema::hasTable('item_orders') && Schema::hasColumn('item_orders', 'warranty_expire_at')) {
            Schema::table('item_orders', function (Blueprint $table) {
                $table->dropColumn('warranty_expire_at');
            });
        }

        if (Schema::hasTable('gb_orders') && Schema::hasColumn('gb_orders', 'warranty_expire_at')) {
            Schema::table('gb_orders', function (Blueprint $table) {
                $table->dropColumn('warranty_expire_at');
            });
        }
    }
};
