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
        if (Schema::hasTable('tickets')) {
            return;
        }
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('title');
            $table->string('category', 50);
            $table->string('status', 20)->default('open')->index();
            $table->tinyInteger('priority')->default(0);
            $table->string('order_code')->nullable();
            $table->dateTime('last_message_at')->nullable();
            $table->string('last_reply_by', 10)->nullable();
            $table->integer('unread_for_user')->default(0);
            $table->integer('unread_for_admin')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
