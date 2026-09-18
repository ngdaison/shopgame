<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('colla_transactions', function (Blueprint $table) {
      $table->string('order_id')->nullable()->after('username');
      $table->json('payment_info')->nullable()->after('description');
      $table->text('user_note')->nullable()->after('payment_info');
      $table->text('sys_note')->nullable()->after('user_note');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('colla_transactions', function (Blueprint $table) {
      $table->dropColumn(['order_id', 'payment_info', 'user_note', 'sys_note']);
    });
  }
};
