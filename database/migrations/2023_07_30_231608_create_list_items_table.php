<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('list_items', function (Blueprint $table) {
      $table->id();
      $table->string('name')->nullable();
      $table->string('type')->default('account');
      $table->string('code')->unique();
      $table->string('image');
      $table->double('cost')->default(0);
      $table->double('price')->default(0);
      $table->string('domain')->nullable();
      $table->integer('discount')->default(0);
      $table->boolean('status')->default(false);
      $table->json('list_image');
      $table->string('currency')->default('VND');
      $table->longText('description')->nullable();
      $table->string('username')->nullable();
      $table->string('password')->nullable();
      $table->longText('extra_data')->nullable();
      $table->json('highlights')->nullable();
      $table->integer('priority')->default(0);
      $table->json('list_skin')->nullable();
      $table->text('raw_skins')->nullable();
      $table->json('list_champ')->nullable();
      $table->text('raw_champions')->nullable();
      // dot kich
      $table->string('cf_the_loai')->nullable();
      $table->integer('cf_vip_ingame')->nullable();
      $table->integer('cf_vip_amount')->default(0);
      //
      $table->integer('group_id');
      $table->string('buyer_name')->nullable();
      $table->string('buyer_code')->nullable();
      $table->double('buyer_paym')->default(0);
      $table->timestamp('buyer_date')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('list_items');
  }
};
