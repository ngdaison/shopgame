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
        $columns = [
            'logo_light',
            'logo_dark',
            'favicon',
            'logo_share',
            'banner',
            'domain',
        ];

        $toDrop = [];
        foreach ($columns as $column) {
            if (Schema::hasColumn('languages', $column)) {
                $toDrop[] = $column;
            }
        }

        if (!empty($toDrop)) {
            Schema::table('languages', function (Blueprint $table) use ($toDrop) {
                $table->dropColumn($toDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('logo_light')->nullable();
            $table->string('logo_dark')->nullable();
            $table->string('favicon')->nullable();
            $table->string('logo_share')->nullable();
            $table->string('banner')->nullable();
            $table->string('domain')->nullable();
        });
    }
};
