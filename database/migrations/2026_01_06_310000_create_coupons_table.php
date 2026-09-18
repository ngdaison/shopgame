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
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('coupon_code', 190)->unique();
                $table->text('product_ids')->nullable(); // JSON list of product/group IDs
                $table->integer('quantity')->default(0); // 0 = unlimited
                $table->integer('used')->default(0);
                $table->enum('discount_type', ['percentage', 'amount']);
                $table->decimal('discount_value', 10, 2);
                $table->decimal('min_order_value', 10, 2)->nullable();
                $table->dateTime('start_datetime')->nullable();
                $table->dateTime('end_datetime')->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

                $table->index('coupon_code');
                $table->index('start_datetime');
                $table->index('end_datetime');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
