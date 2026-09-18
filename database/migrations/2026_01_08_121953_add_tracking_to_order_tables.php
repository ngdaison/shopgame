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
        $tables = ['list_items', 'resource_v2_s', 'item_orders', 'bulk_orders', 'g_b_orders'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('buyer_ip')->nullable();
                $table->string('buyer_ua')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['list_items', 'resource_v2_s', 'item_orders', 'bulk_orders', 'g_b_orders'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['buyer_ip', 'buyer_ua']);
            });
        }
    }
};
