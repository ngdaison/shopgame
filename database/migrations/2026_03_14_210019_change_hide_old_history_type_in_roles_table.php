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
        // Store which roles had hide_old_history = 1
        $roleIdsWithLimit = DB::table('roles')->where('hide_old_history', 1)->pluck('id')->toArray();
        $roleInfo = DB::table('roles')->get(['id', 'created_at'])->keyBy('id');

        // Step 1: Make it nullable tinyint
        Schema::table('roles', function (Blueprint $table) {
            $table->tinyInteger('hide_old_history')->nullable()->change();
        });

        // Step 2: Set all to null
        DB::table('roles')->update(['hide_old_history' => null]);

        // Step 3: Change type to date
        Schema::table('roles', function (Blueprint $table) {
            $table->date('hide_old_history')->nullable()->default(null)->change();
        });

        // Step 4: Restore values as dates
        foreach ($roleIdsWithLimit as $id) {
            $createdAt = $roleInfo[$id]->created_at;
            $date = date('Y-m-d', strtotime($createdAt));
            DB::table('roles')->where('id', $id)->update(['hide_old_history' => $date]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('hide_old_history')->default(false)->change();
        });
    }
};
