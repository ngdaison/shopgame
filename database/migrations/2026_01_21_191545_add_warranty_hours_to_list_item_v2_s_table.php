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
        if (!Schema::hasColumn('list_item_v2_s', 'warranty_hours')) {
            Schema::table('list_item_v2_s', function (Blueprint $table) {
                $table->integer('warranty_hours')->default(0)->after('priority');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->dropColumn('warranty_hours');
        });
    }
};
