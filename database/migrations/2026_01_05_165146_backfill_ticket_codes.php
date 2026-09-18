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
        $tickets = \Illuminate\Support\Facades\DB::table('tickets')->whereNull('code')->get();
        foreach ($tickets as $ticket) {
            \Illuminate\Support\Facades\DB::table('tickets')
                ->where('id', $ticket->id)
                ->update(['code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10))]);
        }
    }

    public function down()
    {
        // No strict reverse needed, or set to null? Better leave as is.
    }
};
