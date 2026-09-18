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
        Schema::table('g_b_orders', function (Blueprint $table) {
            $table->string('input_contact')->nullable()->after('input_extra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('g_b_orders', function (Blueprint $table) {
            $table->dropColumn('input_contact');
        });
    }
};
