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
    Schema::create('list_item_archives', function (Blueprint $table) {
      $table->id();
      $table->string('code');
      $table->string('username')->nullable();
      $table->string('password')->nullable();
      $table->text('extra_data')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('list_item_archives');
  }
};
