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
    Schema::create('pin_groups', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('link')->nullable();
      $table->string('image');
      $table->boolean('status')->default(false);
      $table->string('open_type')->default('_self');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('pin_groups');
  }
};
