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
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            if (!Schema::hasColumn('list_item_v2_s', 'api_domain')) {
                $table->string('api_domain')->nullable()->after('client_type');
            }
            if (!Schema::hasColumn('list_item_v2_s', 'api_key')) {
                $table->string('api_key')->nullable()->after('api_domain');
            }
            if (!Schema::hasColumn('list_item_v2_s', 'api_id')) {
                $table->string('api_id')->nullable()->after('api_key');
            }
            if (!Schema::hasColumn('list_item_v2_s', 'api_coupon')) {
                $table->string('api_coupon')->nullable()->after('api_id');
            }
            if (!Schema::hasColumn('list_item_v2_s', 'api_config_id')) {
                $table->unsignedBigInteger('api_config_id')->nullable()->after('api_coupon');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            if (Schema::hasColumn('list_item_v2_s', 'api_domain')) {
                $table->dropColumn('api_domain');
            }
            if (Schema::hasColumn('list_item_v2_s', 'api_key')) {
                $table->dropColumn('api_key');
            }
            if (Schema::hasColumn('list_item_v2_s', 'api_id')) {
                $table->dropColumn('api_id');
            }
            if (Schema::hasColumn('list_item_v2_s', 'api_coupon')) {
                $table->dropColumn('api_coupon');
            }
            if (Schema::hasColumn('list_item_v2_s', 'api_config_id')) {
                $table->dropColumn('api_config_id');
            }
        });
    }
};
