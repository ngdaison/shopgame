<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('affiliates', 'user_id')) {
            Schema::table('affiliates', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            });

            // Backfill user_id based on username
            $affiliates = DB::table('affiliates')->get();
            foreach ($affiliates as $affiliate) {
                $user = DB::table('users')->where('username', $affiliate->username)->first();
                if ($user) {
                    DB::table('affiliates')->where('id', $affiliate->id)->update(['user_id' => $user->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
