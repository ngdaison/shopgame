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
    Schema::table('spin_quest_logs', function (Blueprint $table) {
      //is_fake_data
      $table->boolean('is_fake_data')->default(false)->after('username');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('spin_quest_logs', function (Blueprint $table) {
      //
      $table->dropColumn('is_fake_data');
    });
  }
};
