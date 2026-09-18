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
        // Add image/cover column to categories if not exists (some module categories might have had images if extended)
        if (!Schema::hasColumn('categories', 'image')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('image')->nullable()->after('slug');
            });
        }

        // Migrate GB Categories (Boosting)
        if (Schema::hasTable('g_b_categories')) {
            $gbCategories = DB::table('g_b_categories')->get();
            foreach ($gbCategories as $cat) {
                $newId = DB::table('categories')->insertGetId([
                    'name' => $cat->name,
                    'slug' => 'boosting-' . $cat->slug,
                    'status' => $cat->status,
                    'username' => $cat->username,
                    'priority' => $cat->priority,
                    'created_at' => $cat->created_at,
                    'updated_at' => $cat->updated_at,
                ]);
                DB::table('g_b_groups')->where('category_id', $cat->id)->update(['category_id' => $newId]);
            }
            Schema::dropIfExists('g_b_categories');
        }

        // Migrate Item Categories
        if (Schema::hasTable('item_categories')) {
            $itemCategories = DB::table('item_categories')->get();
            foreach ($itemCategories as $cat) {
                $newId = DB::table('categories')->insertGetId([
                    'name' => $cat->name,
                    'slug' => 'item-' . $cat->slug,
                    'status' => $cat->status,
                    'username' => $cat->username,
                    'priority' => $cat->priority,
                    'created_at' => $cat->created_at,
                    'updated_at' => $cat->updated_at,
                ]);
                DB::table('item_groups')->where('category_id', $cat->id)->update(['category_id' => $newId]);
            }
            Schema::dropIfExists('item_categories');
        }

        // Migrate Category V2 (Shop Nick V2)
        if (Schema::hasTable('category_v2_s')) {
            $v2Categories = DB::table('category_v2_s')->get();
            foreach ($v2Categories as $cat) {
                $newId = DB::table('categories')->insertGetId([
                    'name' => $cat->name,
                    'slug' => 'v2-' . $cat->slug,
                    'status' => $cat->status,
                    'username' => $cat->username,
                    'priority' => $cat->priority,
                    'created_at' => $cat->created_at,
                    'updated_at' => $cat->updated_at,
                ]);
                DB::table('group_v2_s')->where('category_id', $cat->id)->update(['category_id' => $newId]);
            }
            Schema::dropIfExists('category_v2_s');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal is complex because we dropped tables. 
        // In a real scenario, we might want to recreate them, but for this task, 
        // we assume the unification is permanent as requested by "xóa tất cả".
    }
};
