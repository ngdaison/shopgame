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
        Schema::table('account_v2_apis', function (Blueprint $table) {
            if (!Schema::hasColumn('account_v2_apis', 'coupon')) {
                $table->string('coupon')->nullable()->after('api_key');
            }
            if (Schema::hasColumn('account_v2_apis', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_v2_apis', function (Blueprint $table) {
            $table->integer('discount')->default(0)->after('api_key');
            $table->dropColumn('coupon');
        });
    }
};
