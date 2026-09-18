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
        if (!Schema::hasColumn('coupons', 'user_usage_limit')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->integer('user_usage_limit')->default(0)->after('quantity')->comment('0 means unlimited for specific user');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('user_usage_limit');
        });
    }
};
