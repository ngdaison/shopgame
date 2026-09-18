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
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
        Schema::table('group_v2_s', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
        Schema::table('item_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
        Schema::table('g_b_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
        Schema::table('group_v2_s', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
        Schema::table('item_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
        Schema::table('g_b_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
    }
};
