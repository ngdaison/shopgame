<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tracking_code')->unique();
            $table->string('referral_link')->nullable();
            $table->string('type')->default('tracking'); // tracking, affiliate
            $table->boolean('status')->default(true);
            
            // Commission Config
            $table->double('commission_percent', 15, 2)->default(0);
            $table->string('limit_type')->default('days'); // days, days_times
            $table->integer('limit_days')->default(0);
            $table->integer('limit_times')->default(0);

            // Statistics (Cached)
            $table->integer('clicks')->default(0);
            $table->integer('registrations')->default(0);
            $table->integer('orders')->default(0);
            $table->double('total_commission', 15, 2)->default(0);
            $table->double('balance', 15, 2)->default(0);
            $table->double('withdrawn', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
