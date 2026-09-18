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
        if (!Schema::hasTable('security_settings')) {
            Schema::create('security_settings', function (Blueprint $table) {
                $table->id();
                $table->string('setting_key', 190)->unique();
                $table->longText('setting_value')->nullable(); // Store JSON
                $table->dateTime('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('security_bans')) {
            Schema::create('security_bans', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['user', 'ip']);
                $table->string('username', 190)->nullable();
                $table->string('ip', 64)->nullable();
                $table->string('reason', 255)->nullable();
                $table->integer('strikes')->default(0);
                $table->enum('status', ['ban', 'ban_1_day', 'ban_2_day', 'ban_3_day', 'ban_4_day'])->default('ban');
                $table->dateTime('banned_until')->nullable();
                $table->dateTime('created_at');
                $table->dateTime('updated_at')->nullable();

                $table->index(['type', 'username']);
                $table->index(['type', 'ip']);
                $table->index('banned_until');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_settings');
        Schema::dropIfExists('security_bans');
    }
};
