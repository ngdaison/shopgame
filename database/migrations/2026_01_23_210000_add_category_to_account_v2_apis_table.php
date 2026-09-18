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
        Schema::table('account_v2_apis', function (Blueprint $table) {
            if (!Schema::hasColumn('account_v2_apis', 'category')) {
                $table->string('category')->nullable()->after('type')->comment('Danh mục sản phẩm');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_v2_apis', function (Blueprint $table) {
            if (Schema::hasColumn('account_v2_apis', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
