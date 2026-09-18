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
    Schema::create('inventory_logs', function (Blueprint $table) {
      $table->id();
      $table->string('unit');
      $table->integer('unit_id');

      $table->string('type');
      $table->integer('value');
      $table->string('content');

      $table->integer('after_value');
      $table->integer('before_value');


      $table->integer('user_id');
      $table->string('username');

      $table->string('source');
      $table->integer('source_id');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('inventory_logs');
  }
};
