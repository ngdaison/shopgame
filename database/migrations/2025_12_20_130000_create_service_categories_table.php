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
        if (!Schema::hasTable('service_categories')) {
            Schema::create('service_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sub_name')->nullable();
                $table->string('image')->nullable();
                $table->boolean('status')->default(true);
                $table->integer('priority')->default(0);
                $table->string('product_type')->default('category'); // 'spin' or 'category'
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('service_category_group')) {
            Schema::create('service_category_group', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('service_category_id');
                $table->unsignedBigInteger('group_id'); // Assuming linking to 'groups' table
                $table->timestamps();

                $table->unique(['service_category_id', 'group_id']);
                // Foreign keys can be added if strict integrity is needed, 
                // but often omitted in flexible CMS if tables change often. 
                // Will add for safety.
                $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('cascade');
                // Assuming 'groups' is the table for App\Models\Group
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade'); 
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
        Schema::dropIfExists('service_category_group');
        Schema::dropIfExists('service_categories');
    }
};
