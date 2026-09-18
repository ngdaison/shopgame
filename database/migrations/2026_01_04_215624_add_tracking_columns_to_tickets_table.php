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
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'last_reply_by')) {
                $table->string('last_reply_by')->nullable()->default('user')->after('status');
            }
            if (!Schema::hasColumn('tickets', 'last_message_at')) {
                $table->timestamp('last_message_at')->nullable()->useCurrent()->after('last_reply_by');
            }
            if (!Schema::hasColumn('tickets', 'unread_for_user')) {
                $table->integer('unread_for_user')->default(0)->after('last_message_at');
            }
            if (!Schema::hasColumn('tickets', 'unread_for_admin')) {
                $table->integer('unread_for_admin')->default(0)->after('unread_for_user');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['last_reply_by', 'last_message_at', 'unread_for_user', 'unread_for_admin']);
        });
    }
};
