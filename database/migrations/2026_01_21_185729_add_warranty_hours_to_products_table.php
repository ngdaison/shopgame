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
        Schema::table('g_b_products', function (Blueprint $table) {
            $table->integer('warranty_hours')->nullable()->default(0)->after('price');
        });

        Schema::table('item_data', function (Blueprint $table) {
            $table->integer('warranty_hours')->nullable()->default(0)->after('price');
        });

        Schema::table('list_items', function (Blueprint $table) {
            $table->integer('warranty_hours')->nullable()->default(0)->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('g_b_products', function (Blueprint $table) {
            $table->dropColumn('warranty_hours');
        });

        Schema::table('item_data', function (Blueprint $table) {
            $table->dropColumn('warranty_hours');
        });

        Schema::table('list_items', function (Blueprint $table) {
            $table->dropColumn('warranty_hours');
        });
    }
};
