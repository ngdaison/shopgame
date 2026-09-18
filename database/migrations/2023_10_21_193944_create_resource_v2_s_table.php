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
    Schema::create('resource_v2_s', function (Blueprint $table) {
      $table->id();
      $table->string('code');
      $table->string('type')->default('account');
      $table->string('domain')->nullable();
      $table->boolean('is_bulk')->default(0);
      $table->string('group_id')->nullable();
      $table->string('username');
      $table->string('password');
      $table->string('extra_data', 1024)->nullable();
      $table->string('buyer_name')->nullable();
      $table->string('buyer_code')->nullable();
      $table->string('buyer_paym')->nullable();
      $table->string('buyer_date')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('resource_v2_s');
  }
};
