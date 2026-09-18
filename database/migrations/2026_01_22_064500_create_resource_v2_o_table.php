<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('resource_v2_o')) {
            Schema::create('resource_v2_o', function (Blueprint $table) {
            $table->id();
            $table->string('code')->index();
            $table->string('type')->default('account');
            $table->string('domain')->nullable();
            $table->boolean('is_bulk')->default(0);
            $table->string('group_id')->nullable()->index();
            $table->text('username')->nullable();
            $table->string('buyer_name')->nullable()->index();
            $table->string('buyer_code')->nullable()->index();
            $table->string('buyer_paym')->nullable();
            $table->timestamp('buyer_date')->nullable();
            $table->string('order_status')->default('Completed');
            $table->timestamp('warranty_expire_at')->nullable();
            $table->integer('warranty_hours')->default(0);
            $table->text('order_note')->nullable();
            $table->string('buyer_ip')->nullable();
            $table->string('buyer_ua')->nullable();
            $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_v2_o');
    }
};
