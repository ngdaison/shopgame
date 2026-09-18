<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('username');
      $table->string('password');
      $table->string('fullname')->nullable();
      $table->string('email')->unique();
      $table->string('phone')->nullable();
      $table->string('avatar')->nullable();
      $table->double('balance', 20, 2)->default(0);
      $table->double('balance_1', 20, 2)->default(0);
      $table->double('balance_2', 20, 2)->default(0);
      $table->double('total_deposit', 20, 2)->default(0);
      $table->double('total_withdraw', 20, 2)->default(0);
      $table->string('status')->default('active');
      $table->string('role')->default('user');

      $table->string('colla_type')->nullable();
      $table->integer('colla_percent')->default(0);
      $table->integer('colla_balance')->default(0);
      $table->integer('colla_pending')->default(0);
      $table->integer('colla_withdraw')->default(0);

      $table->string('referral_by')->nullable();
      $table->string('referral_code')->nullable();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('access_token')->nullable();
      $table->string('ip_address')->nullable();
      $table->timestamp('last_action')->nullable();
      $table->string('register_by')->nullable();
      $table->string('register_ip')->nullable();
      $table->boolean('received_gift')->default(0);
      $table->rememberToken();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
