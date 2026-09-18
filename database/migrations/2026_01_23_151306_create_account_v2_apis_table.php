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
        if (!Schema::hasTable('account_v2_apis')) {
            Schema::create('account_v2_apis', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->default('ShopClone7');
                $table->string('url');
                $table->string('api_key');
                $table->integer('discount')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_v2_apis');
    }
};
