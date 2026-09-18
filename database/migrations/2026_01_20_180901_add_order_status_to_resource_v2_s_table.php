<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('resource_v2_s', 'order_status')) {
            Schema::table('resource_v2_s', function (Blueprint $table) {
                $table->string('order_status', 20)->default('Completed')->after('buyer_date');
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
        Schema::table('resource_v2_s', function (Blueprint $table) {
            $table->dropColumn('order_status');
        });
    }
};
