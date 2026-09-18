<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Stores linked social accounts: { "google": "uid", "facebook": "uid", "discord": "uid" }
            $table->json('social_links')->nullable()->after('register_by');
            // Has password flag (true if user has a real password, false for pure social signups)
            $table->boolean('has_password')->default(true)->after('social_links');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['social_links', 'has_password']);
        });
    }
};
