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
        if (!Schema::hasColumn('service_categories', 'cover')) {
            Schema::table('service_categories', function (Blueprint $column) {
                $column->string('cover')->nullable()->after('image');
                $column->bigInteger('price')->default(0)->after('cover');
                $column->json('prizes')->nullable()->after('price');
                $column->longText('descr')->nullable()->after('prizes');
                $column->bigInteger('invar_id')->nullable()->after('descr');
                $column->integer('play_times')->default(0)->after('invar_id');
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
        Schema::table('service_categories', function (Blueprint $column) {
            $column->dropColumn(['cover', 'price', 'prizes', 'descr', 'invar_id', 'play_times']);
        });
    }
};
