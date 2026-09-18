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
    Schema::create('spin_quests', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('type')->default('custom');
      $table->json('prizes');
      $table->string('image');
      $table->string('cover');
      $table->longText('descr')->nullable();
      $table->double('price');
      $table->integer('store_id')->nullable();
      $table->boolean('status')->default(true);
      $table->integer('priority')->default(0);
      $table->integer('invar_id')->nullable();
      $table->integer('play_times')->default(1);
      $table->integer('category_id')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('spin_quests');
  }
};
