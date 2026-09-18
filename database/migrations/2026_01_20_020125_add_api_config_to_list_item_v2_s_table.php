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
            $table->string('client_type')->default('normal')->after('status');
            $table->string('api_domain')->nullable()->after('client_type');
            $table->string('api_key')->nullable()->after('api_domain');
            $table->string('api_id')->nullable()->after('api_key');
            $table->string('api_coupon')->nullable()->after('api_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'api_domain', 'api_key', 'api_id', 'api_coupon']);
        });
    }
};
