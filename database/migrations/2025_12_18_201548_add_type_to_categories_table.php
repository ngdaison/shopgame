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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
        });

        // Populate type based on slug prefixes from previous migration
        DB::table('categories')->where('slug', 'like', 'boosting-%')->update(['type' => 'boosting']);
        DB::table('categories')->where('slug', 'like', 'item-%')->update(['type' => 'item']);
        DB::table('categories')->where('slug', 'like', 'v2-%')->update(['type' => 'accountv2']);
        
        // Remaining categories without these prefixes are likely original Account categories
        DB::table('categories')->whereNull('type')->update(['type' => 'account']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
