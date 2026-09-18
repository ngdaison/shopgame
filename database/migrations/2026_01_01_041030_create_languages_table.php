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
        if (!Schema::hasTable('languages')) {
            Schema::create('languages', function (Blueprint $table) {
                $table->id();
                $table->string('domain')->nullable()->comment('Domain specific language, null for global');
                $table->string('name');
                $table->string('iso_code')->index();
                $table->boolean('status')->default(true);
                $table->boolean('is_default')->default(false);
                
                // UI Overrides
                $table->string('logo_light')->nullable();
                $table->string('logo_dark')->nullable();
                $table->string('favicon')->nullable();
                $table->string('logo_share')->nullable();
                $table->string('banner')->nullable();
                
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('language_translations')) {
            Schema::create('language_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('language_id')->constrained('languages')->onDelete('cascade');
                $table->string('key')->index(); // Source text
                $table->text('value')->nullable(); // Translated text
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('language_translations');
        Schema::dropIfExists('languages');
    }
};
