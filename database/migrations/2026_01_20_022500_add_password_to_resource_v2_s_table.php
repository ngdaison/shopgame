<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('resource_v2_s')) {
            Schema::table('resource_v2_s', function (Blueprint $table) {
                if (!Schema::hasColumn('resource_v2_s', 'password')) {
                    $table->string('password')->after('username')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resource_v2_s', function (Blueprint $table) {
            if (Schema::hasColumn('resource_v2_s', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
};
