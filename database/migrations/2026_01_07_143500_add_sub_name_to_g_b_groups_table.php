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
    if (!Schema::hasColumn('g_b_groups', 'sub_name')) {
      Schema::table('g_b_groups', function (Blueprint $table) {
        $table->string('sub_name')->nullable()->after('name');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('g_b_groups', function (Blueprint $table) {
      $table->dropColumn('sub_name');
    });
  }
};
