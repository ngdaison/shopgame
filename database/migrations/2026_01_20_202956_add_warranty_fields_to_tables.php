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
        // Account V2 Groups
        if (Schema::hasTable('group_v2_s') && !Schema::hasColumn('group_v2_s', 'warranty_hours')) {
            Schema::table('group_v2_s', function (Blueprint $table) {
                $table->integer('warranty_hours')->nullable()->default(0);
            });
        }

        // Service Categories (Gói)
        if (Schema::hasTable('service_categories') && !Schema::hasColumn('service_categories', 'warranty_hours')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->integer('warranty_hours')->nullable()->default(0);
            });
        }

        // Item Groups (Vật phẩm)
        if (Schema::hasTable('item_groups') && !Schema::hasColumn('item_groups', 'warranty_hours')) {
            Schema::table('item_groups', function (Blueprint $table) {
                $table->integer('warranty_hours')->nullable()->default(0);
            });
        }

        // Account V2 Items
        if (Schema::hasTable('list_item_v2_s') && !Schema::hasColumn('list_item_v2_s', 'warranty_expire_at')) {
            Schema::table('list_item_v2_s', function (Blueprint $table) {
                $table->timestamp('warranty_expire_at')->nullable();
            });
        }
        
        // Transactions
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'warranty_expire_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->timestamp('warranty_expire_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         if (Schema::hasTable('group_v2_s')) {
            Schema::table('group_v2_s', function (Blueprint $table) {
                $table->dropColumn('warranty_hours');
            });
         }
         if (Schema::hasTable('service_categories')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropColumn('warranty_hours');
            });
         }
         if (Schema::hasTable('item_groups')) {
            Schema::table('item_groups', function (Blueprint $table) {
                $table->dropColumn('warranty_hours');
            });
         }
         if (Schema::hasTable('list_item_v2_s')) {
            Schema::table('list_item_v2_s', function (Blueprint $table) {
                $table->dropColumn('warranty_expire_at');
            });
         }
         if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('warranty_expire_at');
            });
         }
    }
};
