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
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'tracking_code')) {
                $table->string('tracking_code')->unique()->after('name');
            }
            if (!Schema::hasColumn('campaigns', 'referral_link')) {
                $table->string('referral_link')->nullable()->after('tracking_code');
            }
            if (!Schema::hasColumn('campaigns', 'commission_type')) {
                $table->string('commission_type')->default('deposit')->after('referral_link');
            }
            if (!Schema::hasColumn('campaigns', 'status')) {
                $table->boolean('status')->default(true)->after('type');
            }
            if (!Schema::hasColumn('campaigns', 'comm_percent')) {
                $table->double('comm_percent', 15, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('campaigns', 'limit_mode')) {
                $table->string('limit_mode')->default('count')->after('comm_percent');
            }
            if (!Schema::hasColumn('campaigns', 'limit_days')) {
                $table->integer('limit_days')->default(0)->after('limit_mode');
            }
            if (!Schema::hasColumn('campaigns', 'limit_count')) {
                $table->integer('limit_count')->default(0)->after('limit_days');
            }
            if (!Schema::hasColumn('campaigns', 'clicks')) {
                $table->integer('clicks')->default(0)->after('limit_count');
            }
            if (!Schema::hasColumn('campaigns', 'registrations')) {
                $table->integer('registrations')->default(0)->after('clicks');
            }
            if (!Schema::hasColumn('campaigns', 'orders')) {
                $table->integer('orders')->default(0)->after('registrations');
            }
            if (!Schema::hasColumn('campaigns', 'total_commission')) {
                $table->double('total_commission', 15, 2)->default(0)->after('orders');
            }
            if (!Schema::hasColumn('campaigns', 'balance')) {
                $table->double('balance', 15, 2)->default(0)->after('total_commission');
            }
            if (!Schema::hasColumn('campaigns', 'withdrawn')) {
                $table->double('withdrawn', 15, 2)->default(0)->after('balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'tracking_code', 'referral_link', 'commission_type', 'status', 'comm_percent', 
                'limit_mode', 'limit_days', 'limit_count', 'clicks', 
                'registrations', 'orders', 'total_commission', 'balance', 'withdrawn'
            ]);
        });
    }
};
