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
        Schema::create('language_domain_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->unsignedBigInteger('domain_id'); // Relating to domain_settings or just storing string? 
            // User requirement: "The domain list is sourced from the domains table (the same data used by /admin/domain)".
            // In typical setup here, /admin/domain writes to `domain_settings` (as per previous context).
            // So domain_id relates to `domain_settings.id` or `domain_settings.domain`?
            // "Add a pivot/config table like: language_domain_settings columns: id, language_id, domain_id"
            // Let's assume domain_id FK to domain_settings(id).
            
            $table->json('config_json')->nullable(); // For flexible overrides
            $table->timestamps();

            $table->unique(['language_id', 'domain_id'], 'lang_domain_unique');
            
            // Foreign keys
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('domain_id')->references('id')->on('domain_settings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_domain_settings');
    }
};
