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
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->decimal('rate', 30, 12)->default(1);
                $table->unsignedTinyInteger('decimals')->default(0);
                $table->string('symbol_left')->nullable();
                $table->string('symbol_right')->nullable();
                $table->string('separator')->nullable()->comment('Thousand separator');
                $table->boolean('status')->default(true);
                $table->boolean('is_default')->default(false);
                $table->enum('rate_mode', ['manual', 'auto'])->default('manual');
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });

            // Seed Default Currency (VND)
            DB::table('currencies')->insert([
                'name' => 'Vietnamese Dong',
                'code' => 'VND',
                'rate' => 1.000000000000,
                'decimals' => 0,
                'symbol_right' => '₫',
                'separator' => '.',
                'status' => true,
                'is_default' => true,
                'rate_mode' => 'manual',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
