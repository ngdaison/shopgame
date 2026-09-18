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
    Schema::table('list_items', function (Blueprint $table) {
      //
      $table->string('staff_name')->nullable();
      $table->string('staff_status')->default('Pending');
      $table->integer('staff_payment')->nullable();
      $table->dateTime('staff_completed_at')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('list_items', function (Blueprint $table) {
      //
      $table->dropColumn('staff_name');
      $table->dropColumn('staff_status');
      $table->dropColumn('staff_payment');
      $table->dropColumn('staff_completed_at');
    });
  }
};
