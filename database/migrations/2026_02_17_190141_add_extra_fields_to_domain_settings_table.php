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
            $table->text('footer_text_1')->nullable();
            $table->text('footer_text_2')->nullable();
            $table->text('dashboard_text_1')->nullable();
            $table->text('notice_homepage')->nullable();
            $table->text('notice_featured_homepage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_settings', function (Blueprint $table) {
            $table->dropColumn([
                'footer_text_1',
                'footer_text_2',
                'dashboard_text_1',
                'notice_homepage',
                'notice_featured_homepage'
            ]);
        });
    }
};
