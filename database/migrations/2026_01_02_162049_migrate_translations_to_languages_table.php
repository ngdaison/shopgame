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
        // 1. Add translations column to languages table
        if (!Schema::hasColumn('languages', 'translations')) {
            Schema::table('languages', function (Blueprint $table) {
                $table->longText('translations')->nullable()->after('is_default');
            });
        }

        // 2. Migrate data
        if (Schema::hasTable('language_translations')) {
            $allTranslations = DB::table('language_translations')->get();
            
            $grouped = [];
            foreach ($allTranslations as $t) {
                $grouped[$t->language_id][$t->key] = $t->value;
            }

            foreach ($grouped as $langId => $trans) {
                DB::table('languages')->where('id', $langId)->update([
                    'translations' => json_encode($trans, JSON_UNESCAPED_UNICODE)
                ]);
            }

            // 3. Drop the old table
            Schema::dropIfExists('language_translations');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // To reverse, we'd need to recreate the table and re-migrate data back
        Schema::create('language_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->text('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $langs = DB::table('languages')->get();
        foreach ($langs as $lang) {
            if ($lang->translations) {
                $trans = json_decode($lang->translations, true);
                if (is_array($trans)) {
                    foreach ($trans as $key => $val) {
                        DB::table('language_translations')->insert([
                            'language_id' => $lang->id,
                            'key' => $key,
                            'value' => $val,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('translations');
        });
    }
};
