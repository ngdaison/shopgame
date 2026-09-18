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
        // Rename order_status to status in list_items
        if (Schema::hasColumn('list_items', 'order_status') && !Schema::hasColumn('list_items', 'status')) {
            DB::statement('ALTER TABLE list_items CHANGE order_status status VARCHAR(20) DEFAULT "Completed"');
        }
        
        // Rename order_status to status in resource_v2_s
        if (Schema::hasColumn('resource_v2_s', 'order_status') && !Schema::hasColumn('resource_v2_s', 'status')) {
            DB::statement('ALTER TABLE resource_v2_s CHANGE order_status status VARCHAR(20) DEFAULT "Completed"');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE list_items CHANGE status order_status VARCHAR(20) DEFAULT "Completed"');
        DB::statement('ALTER TABLE resource_v2_s CHANGE status order_status VARCHAR(20) DEFAULT "Completed"');
    }
};
