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
        // Drop password and extra_data from resource_v2_s (inventory) only
        Schema::table('resource_v2_s', function (Blueprint $table) {
            if (Schema::hasColumn('resource_v2_s', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn('resource_v2_s', 'extra_data')) {
                $table->dropColumn('extra_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add password and extra_data to resource_v2_s only
        Schema::table('resource_v2_s', function (Blueprint $table) {
            $table->text('password')->nullable();
            $table->text('extra_data')->nullable();
        });
    }
};
