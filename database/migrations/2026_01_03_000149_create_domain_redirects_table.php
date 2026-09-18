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
        if (!Schema::hasTable('domain_redirects')) {
            Schema::create('domain_redirects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('source_domain_id')->unique()->constrained('domain_settings')->cascadeOnDelete();
                $table->foreignId('target_domain_id')->constrained('domain_settings')->cascadeOnDelete();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_redirects');
    }
};
