<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->boolean('allow_preorder')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('list_item_v2_s', function (Blueprint $table) {
            $table->dropColumn('allow_preorder');
        });
    }
};
