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
        // 1. Add 'domain' to users table (to link Partner to a specific domain)
        if (!Schema::hasColumn('users', 'domain')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('domain')->nullable()->index()->after('username');
            });
        }

        // 2. Add 'domain' to invoices table (if missing)
        if (!Schema::hasColumn('invoices', 'domain')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('domain')->nullable()->index()->after('user_id');
            });
        }

        // 3. Add Settings columns to domain_settings table
        Schema::table('domain_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('domain_settings', 'primary_color')) {
                $table->string('primary_color')->nullable();
            }
            if (!Schema::hasColumn('domain_settings', 'font')) {
                $table->string('font')->nullable();
            }
            if (!Schema::hasColumn('domain_settings', 'intro_text')) {
                $table->text('intro_text')->nullable(); // Thông Tin Giới Thiệu
            }
            if (!Schema::hasColumn('domain_settings', 'buy_button_text')) {
                $table->string('buy_button_text')->nullable();
            }
            if (!Schema::hasColumn('domain_settings', 'buy_button_image')) {
                $table->string('buy_button_image')->nullable(); // Link ảnh nút mua
            }
            if (!Schema::hasColumn('domain_settings', 'show_banner_top')) {
                $table->boolean('show_banner_top')->default(0); // Hiện Banner và TOP Nạp
            }
            if (!Schema::hasColumn('domain_settings', 'show_run_notify')) {
                $table->boolean('show_run_notify')->default(0); // Hiện Thông Báo Chạy
            }
            if (!Schema::hasColumn('domain_settings', 'analytics_tags')) {
                $table->text('analytics_tags')->nullable(); // Thẻ Thống Kê
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('domain');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('domain');
        });

        Schema::table('domain_settings', function (Blueprint $table) {
            $table->dropColumn([
                'font',
                'intro_text',
                'buy_button_text',
                'buy_button_image',
                'show_banner_top',
                'show_run_notify',
                'analytics_tags'
            ]);
        });
    }
};
