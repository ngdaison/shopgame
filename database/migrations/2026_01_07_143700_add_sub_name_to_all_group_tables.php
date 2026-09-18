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
    // Add sub_name to groups table
    if (!Schema::hasColumn('groups', 'sub_name')) {
      Schema::table('groups', function (Blueprint $table) {
        $table->string('sub_name')->nullable()->after('name');
      });
    }

    // Add sub_name to group_v2_s table
    if (!Schema::hasColumn('group_v2_s', 'sub_name')) {
      Schema::table('group_v2_s', function (Blueprint $table) {
        $table->string('sub_name')->nullable()->after('name');
      });
    }

    // Add sub_name to service_categories table
    if (!Schema::hasColumn('service_categories', 'sub_name')) {
      Schema::table('service_categories', function (Blueprint $table) {
        $table->string('sub_name')->nullable()->after('name');
      });
    }

    // Add sub_name to categories table
    if (!Schema::hasColumn('categories', 'sub_name')) {
      Schema::table('categories', function (Blueprint $table) {
        $table->string('sub_name')->nullable()->after('name');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('groups', function (Blueprint $table) {
      if (Schema::hasColumn('groups', 'sub_name')) {
        $table->dropColumn('sub_name');
      }
    });

    Schema::table('group_v2_s', function (Blueprint $table) {
      if (Schema::hasColumn('group_v2_s', 'sub_name')) {
        $table->dropColumn('sub_name');
      }
    });

    Schema::table('service_categories', function (Blueprint $table) {
      if (Schema::hasColumn('service_categories', 'sub_name')) {
        $table->dropColumn('sub_name');
      }
    });

    Schema::table('categories', function (Blueprint $table) {
      if (Schema::hasColumn('categories', 'sub_name')) {
        $table->dropColumn('sub_name');
      }
    });
  }
};
