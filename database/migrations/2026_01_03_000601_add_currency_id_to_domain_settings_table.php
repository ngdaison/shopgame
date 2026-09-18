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
        if (!Schema::hasColumn('domain_settings', 'currency_id')) {
            Schema::table('domain_settings', function (Blueprint $table) {
                $table->unsignedInteger('currency_id')->nullable()->after('language_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_settings', function (Blueprint $table) {
            $table->dropColumn('currency_id');
        });
    }
};
