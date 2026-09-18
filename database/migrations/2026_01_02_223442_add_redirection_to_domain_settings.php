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
        if (!Schema::hasColumn('domain_settings', 'is_redirect')) {
            Schema::table('domain_settings', function (Blueprint $table) {
                $table->boolean('is_redirect')->default(false)->after('domain');
                $table->string('redirect_to')->nullable()->after('is_redirect');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_settings', function (Blueprint $table) {
            $table->dropColumn(['is_redirect', 'redirect_to']);
        });
    }
};
