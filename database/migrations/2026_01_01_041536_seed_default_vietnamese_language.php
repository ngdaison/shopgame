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
        \App\Models\Language::updateOrCreate(
            ['iso_code' => 'vi'],
            [
                'name' => 'Tiếng Việt',
                'domain' => null, // Global
                'status' => true,
                'is_default' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Language::where('iso_code', 'vi')->delete();
    }
};
