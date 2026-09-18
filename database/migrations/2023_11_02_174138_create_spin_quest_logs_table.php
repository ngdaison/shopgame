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
    Schema::create('spin_quest_logs', function (Blueprint $table) {
      $table->id();
      $table->string('unit');
      $table->string('prize');
      $table->double('price');
      $table->string('status')->default('pending');
      $table->string('content');
      // $table->foreignId('user_id')->constrained('users');
      $table->integer('user_id');
      $table->string('username');
      // $table->boolean('is_fake_data')->default(false);
      $table->foreignId('spin_quest_id')->constrained('spin_quests');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('spin_quest_logs');
  }
};
