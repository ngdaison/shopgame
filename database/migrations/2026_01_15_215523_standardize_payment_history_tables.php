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
        $tables = ['paypal', 'usdt', 'perfect_money'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                // Add missing columns if they don't exist
                if (!Schema::hasColumn($table->getTable(), 'trans_id')) {
                    $table->string('trans_id')->nullable()->after('transaction_id');
                }
                if (!Schema::hasColumn($table->getTable(), 'content')) {
                    $table->string('content')->nullable()->after('amount');
                }
                if (!Schema::hasColumn($table->getTable(), 'username')) {
                    $table->string('username')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn($table->getTable(), 'balance_before')) {
                    $table->double('balance_before', 20, 2)->default(0)->after('amount');
                }
                if (!Schema::hasColumn($table->getTable(), 'balance_after')) {
                    $table->double('balance_after', 20, 2)->default(0)->after('balance_before');
                }
                if (!Schema::hasColumn($table->getTable(), 'bank_code')) {
                    $table->string('bank_code')->nullable()->after('content');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['paypal', 'usdt', 'perfect_money'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['trans_id', 'content', 'username', 'balance_before', 'balance_after', 'bank_code']);
            });
        }
    }
};
