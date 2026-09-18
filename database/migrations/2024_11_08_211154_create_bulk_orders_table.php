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
    Schema::create('bulk_orders', function (Blueprint $table) {
      $table->id();

      $table->string('name');
      $table->string('code');
      $table->string('type')->nullable();
      $table->string('image')->nullable();
      $table->string('group')->nullable();
      $table->string('domain');
      $table->string('payment');
      $table->integer('user_id');
      $table->string('username');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('bulk_orders');
  }
};
