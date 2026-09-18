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
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->unsignedBigInteger('api_config_id')->nullable()->after('api_id');
            // We might not want to add a strict foreign key if the database engine doesn't support it or if it's already complex
            // But let's add it for consistency if possible.
            // $table->foreign('api_config_id')->references('id')->on('account_v2_apis')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->dropColumn('api_config_id');
        });
    }
};
