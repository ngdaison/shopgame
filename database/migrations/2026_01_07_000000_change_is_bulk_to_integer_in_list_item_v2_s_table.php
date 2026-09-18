<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->integer('is_bulk')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->boolean('is_bulk')->default(false)->change();
        });
    }
};
