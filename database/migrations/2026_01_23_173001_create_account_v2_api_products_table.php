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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_v2_api_products');
    }
};
