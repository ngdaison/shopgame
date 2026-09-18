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
        if (!Schema::hasTable('domain_settings')) {
            Schema::create('domain_settings', function (Blueprint $table) {
                $table->id();
                $table->string('domain')->unique();
                
                // Branding & Visuals
                $table->string('logo_light')->nullable();
                $table->string('logo_dark')->nullable();
                $table->string('favicon')->nullable();
                $table->string('logo_share')->nullable();
                $table->string('default_theme')->default('light');
                $table->string('banner')->nullable();
                $table->string('youtube_id')->nullable();
                $table->string('background_image_url')->nullable();
                
                // SEO & Info
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->text('keywords')->nullable();
                $table->string('admin_email')->nullable();
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_settings');
    }
};
