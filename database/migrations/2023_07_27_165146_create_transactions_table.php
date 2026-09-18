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
    Schema::create('transactions', function (Blueprint $table) {
      $table->id();
      $table->string('code');
      $table->double('amount', 20, 2);
      $table->double('cost_amount', 20, 2)->default(0);
      $table->double('balance_after', 20, 2);
      $table->double('balance_before', 20, 2);
      $table->string('type');
      $table->json('extras')->nullable();
      $table->string('order_id')->nullable();
      $table->string('sys_note')->nullable();
      $table->string('status');
      $table->string('content')->nullable();
      $table->integer('user_id');
      $table->string('username');
      $table->string('domain')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transactions');
  }
};
