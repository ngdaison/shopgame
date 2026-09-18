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
        // Drop the old table
        if (Schema::hasTable('account_v2_api_products')) {
            Schema::dropIfExists('account_v2_api_products');
        }

        // Add products_data column to account_v2_apis to store cached product information
        if (!Schema::hasColumn('account_v2_apis', 'products_data')) {
            Schema::table('account_v2_apis', function (Blueprint $table) {
                $table->json('products_data')->nullable()->after('category')->comment('Cached product data from API');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the old table for rollback (if needed)
        if (!Schema::hasTable('account_v2_api_products')) {
            Schema::create('account_v2_api_products', function (Blueprint $table) {
                $table->id();
                $table->integer('api_config_id');
                $table->string('external_id');
                $table->string('name');
                $table->string('category_name')->nullable();
                $table->string('price')->nullable();
                $table->integer('amount')->default(0);
                $table->timestamps();
            });
        }

        // Remove products_data column
        if (Schema::hasColumn('account_v2_apis', 'products_data')) {
            Schema::table('account_v2_apis', function (Blueprint $table) {
                $table->dropColumn('products_data');
            });
        }
    }
};
