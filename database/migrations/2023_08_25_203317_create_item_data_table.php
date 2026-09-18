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
    Schema::create('item_data', function (Blueprint $table) {
      $table->id();
      $table->string('name')->nullable();
      $table->string('type')->default('item');
      $table->string('code')->unique();
      $table->string('image');
      $table->double('price')->default(0);
      $table->integer('robux')->default(0);
      $table->integer('discount')->default(0);
      $table->boolean('status')->default(false);
      $table->integer('sold_count')->default(0);
      $table->json('highlights')->nullable();
      $table->string('currency')->default('VND');
      $table->integer('ingame_id')->nullable();
      $table->longText('description')->nullable();
      $table->text('extra_data')->nullable();
      $table->integer('priority')->default(0);
      $table->integer('group_id');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('item_data');
  }
};
