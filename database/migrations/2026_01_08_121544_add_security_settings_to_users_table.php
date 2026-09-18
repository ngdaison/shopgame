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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_changed_at')->nullable()->after('email');
            $table->boolean('login_verify_email')->default(false)->after('status');
            $table->boolean('login_verify_google2fa')->default(false)->after('login_verify_email');
            $table->boolean('secure_order_view')->default(false)->after('login_verify_google2fa');
            $table->boolean('notify_login_success')->default(false)->after('secure_order_view');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_changed_at',
                'login_verify_email',
                'login_verify_google2fa',
                'secure_order_view',
                'notify_login_success'
            ]);
        });
    }
};
