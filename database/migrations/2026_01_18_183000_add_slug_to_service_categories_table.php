<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name')->index();
        });

        // Auto-populate slugs for existing records
        $categories = \Illuminate\Support\Facades\DB::table('service_categories')->get();
        foreach ($categories as $category) {
            $slug = Str::slug($category->name);
            // Ensure unique slug if needed, but for now simple slug is fine for this context
            // or we can append ID if duplicate.
            
            // Check for duplicates
            if (\Illuminate\Support\Facades\DB::table('service_categories')->where('slug', $slug)->exists()) {
                $slug = $slug . '-' . $category->id;
            }

            \Illuminate\Support\Facades\DB::table('service_categories')
                ->where('id', $category->id)
                ->update(['slug' => $slug]);
        }
        
        // After populating, we can enforce unique if we wanted, but nullable is safer for now.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
