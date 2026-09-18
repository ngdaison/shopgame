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
    Schema::create('list_item_v2_s', function (Blueprint $table) {
      $table->id();
      $table->string('name')->nullable();
      $table->string('type')->default('account');
      $table->string('code')->unique();
      $table->string('image');
      $table->double('cost')->default(0);
      $table->double('price')->default(0);
      $table->integer('discount')->default(0);
      $table->boolean('status')->default(false);
      $table->boolean('is_bulk')->default(false);
      $table->json('list_image');
      $table->longText('description')->nullable();
      $table->text('extra_data')->nullable();
      $table->json('highlights')->nullable();
      $table->integer('priority')->default(0);
      $table->integer('group_id');
      $table->string('resource_code')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('list_item_v2_s');
  }
};
