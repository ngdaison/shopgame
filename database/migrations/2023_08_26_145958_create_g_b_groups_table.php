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
    Schema::create('g_b_groups', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('image');
      $table->string('type')->default('account');
      $table->string('slug')->unique();
      $table->text('descr')->nullable();
      $table->integer('sold')->default(0);
      $table->string('status')->default('draft');
      $table->integer('priority')->default(0);
      $table->string('username');
      $table->integer('category_id');
      $table->string('category_name');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('g_b_groups');
  }
};