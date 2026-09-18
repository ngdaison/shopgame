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
    Schema::create('withdraw_requests', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->unsignedBigInteger('user_id');
      $table->string('username');
      $table->unsignedBigInteger('var_id')->nullable();
      $table->string('unit')->nullable();
      $table->string('name')->nullable();
      $table->integer('amount')->default(0);
      $table->json('user_inputs')->nullable();
      $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
      $table->text('user_note')->nullable();
      $table->text('admin_note')->nullable();
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('var_id')->references('id')->on('inventory_vars')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('withdraw_requests');
  }
};
