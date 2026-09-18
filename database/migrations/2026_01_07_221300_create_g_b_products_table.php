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
        if (!Schema::hasTable('g_b_products')) {
            Schema::create('g_b_products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('price')->default(0);
                $table->string('code')->nullable();
                $table->text('descr')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('priority')->default(0);
                $table->integer('package_id')->nullable(); 
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g_b_products');
    }
};
