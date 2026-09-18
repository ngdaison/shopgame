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
        // 1. Drop the pivot table
        Schema::dropIfExists('language_domain_settings');

        // 2. Add domain_settings JSON column to languages
        if (!Schema::hasColumn('languages', 'domain_settings')) {
            Schema::table('languages', function (Blueprint $table) {
                $table->json('domain_settings')->nullable()->after('is_default');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('domain_settings');
        });

        // Re-create the pivot table if rolling back
        Schema::create('language_domain_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->unsignedBigInteger('domain_id');
            $table->json('config_json')->nullable();
            $table->timestamps();

            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('domain_id')->references('id')->on('domain_settings')->onDelete('cascade');
            $table->unique(['language_id', 'domain_id']);
        });
    }
};
