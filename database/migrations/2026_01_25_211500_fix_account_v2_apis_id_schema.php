<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to modify column because doctrine/dbal might be missing
        // 1. Check if primary key exists (basic check, hard to do reliably in raw SQL cross-db, assuming MySQL per user context)
        // 2. Modify column to be AUTO_INCREMENT and PRIMARY KEY
        if (Schema::hasTable('account_v2_apis')) {
            // Check if column id is already auto-increment or primary key
            $columns = DB::select('SHOW COLUMNS FROM account_v2_apis LIKE "id"');
            if (empty($columns) || !str_contains($columns[0]->Extra, 'auto_increment')) {
                DB::statement('ALTER TABLE account_v2_apis MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('account_v2_apis')) {
            // Reverting is tricky without dropping AI first
            DB::statement('ALTER TABLE account_v2_apis MODIFY id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE account_v2_apis DROP PRIMARY KEY');
        }
    }
};
