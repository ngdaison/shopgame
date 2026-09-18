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
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('description')->nullable();
                $table->decimal('min_deposit', 15, 2);
                $table->decimal('max_deposit', 15, 2);
                $table->decimal('bonus_value', 15, 2);
                $table->enum('bonus_type', ['percentage', 'fixed']);
                $table->json('payment_methods');
                $table->boolean('status')->default(1); // 1: Active, 0: Inactive
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
