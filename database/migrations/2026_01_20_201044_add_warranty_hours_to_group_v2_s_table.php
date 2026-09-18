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
        if (Schema::hasTable('group_v2_s') && !Schema::hasColumn('group_v2_s', 'warranty_hours')) {
            Schema::table('group_v2_s', function (Blueprint $table) {
                $table->integer('warranty_hours')->nullable()->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('group_v2_s')) {
            Schema::table('group_v2_s', function (Blueprint $table) {
                $table->dropColumn('warranty_hours');
            });
        }
    }
};
