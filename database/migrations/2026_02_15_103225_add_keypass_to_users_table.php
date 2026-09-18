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
            $table->boolean('keypass_enabled')->default(false)->after('login_verify_google2fa');
            $table->string('keypass_hash')->nullable()->after('keypass_enabled');
            $table->timestamp('keypass_changed_at')->nullable()->after('keypass_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['keypass_enabled', 'keypass_hash', 'keypass_changed_at']);
        });
    }
};
