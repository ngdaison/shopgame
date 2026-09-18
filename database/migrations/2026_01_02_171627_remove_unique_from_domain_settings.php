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
        Schema::table('domain_settings', function (Blueprint $table) {
            $table->dropUnique('domain_settings_domain_unique');
            $table->unique(['domain', 'language_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_settings', function (Blueprint $table) {
            $table->dropUnique(['domain', 'language_id']);
            $table->unique('domain');
        });
    }
};
