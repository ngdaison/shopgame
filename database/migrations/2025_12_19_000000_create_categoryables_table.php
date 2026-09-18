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
        if (!Schema::hasTable('categoryables')) {
            Schema::create('categoryables', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('categoryable_id');
                $table->string('categoryable_type');
                $table->timestamps();

                $table->index(['categoryable_id', 'categoryable_type']);
            });
        }

        // Migration logic for existing data
        $groupTypes = [
            'App\Models\Group'     => 'groups',
            'App\Models\GroupV2'   => 'group_v2_s',
            'App\Models\ItemGroup' => 'item_groups',
            'App\Models\GBGroup'   => 'g_b_groups',
        ];

        foreach ($groupTypes as $model => $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'category_id')) {
                $items = DB::table($table)->whereNotNull('category_id')->get();
                foreach ($items as $item) {
                    if (DB::table('categories')->where('id', $item->category_id)->exists()) {
                        DB::table('categoryables')->insert([
                            'category_id'       => $item->category_id,
                            'categoryable_id'   => $item->id,
                            'categoryable_type' => $model,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoryables');
    }
};
