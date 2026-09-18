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
    Schema::create('g_b_packages', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->string('name');
      $table->string('image')->nullable();
      $table->string('input')->default('note');
      $table->double('price');
      $table->text('descr')->nullable();
      $table->boolean('status')->default(true);
      $table->integer('priority')->default(0);
      $table->integer('group_id');
      $table->integer('sold_count')->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('g_b_packages');
  }
};