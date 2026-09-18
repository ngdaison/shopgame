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
        // 1. Rename 'banking' table (Config) to 'bank_config'
        if (Schema::hasTable('banking')) {
            Schema::rename('banking', 'bank_config');
        } elseif (Schema::hasTable('banks')) {
             // Fallback if previous migration step failed or was skipped logic
             Schema::rename('banks', 'bank_config');
        }

        // 2. Create new 'banking' table for History
        if (!Schema::hasTable('banking')) {
            Schema::create('banking', function (Blueprint $table) {
                $table->id();
                $table->string('trans_id')->nullable(); // Transaction Code
                $table->unsignedBigInteger('user_id');
                $table->double('amount', 20, 2);
                $table->double('balance_before', 20, 2)->default(0);
                $table->double('balance_after', 20, 2)->default(0);
                $table->string('content')->nullable();
                $table->string('status')->default('pending');
                $table->string('bank_code')->nullable(); // To link with bank_config
                $table->string('username')->nullable(); // Denormalized for query speed
                $table->timestamps();
            });
        }

        // 3. Migrate Data from 'transactions' to 'banking'
        // Only deposit-bank related
        $transactions = DB::table('transactions')
            ->whereIn('type', ['deposit-bank', 'deposit-banking'])
            ->get();

        foreach ($transactions as $trans) {
            DB::table('banking')->insert([
                'trans_id'       => $trans->code,
                'user_id'        => $trans->user_id,
                'username'       => $trans->username,
                'amount'         => $trans->amount,
                'balance_before' => $trans->balance_before,
                'balance_after'  => $trans->balance_after,
                'content'        => $trans->content,
                'status'         => $trans->status,
                'bank_code'      => $trans->bank_code ?? null, // Uses the column we added earlier
                'created_at'     => $trans->created_at,
                'updated_at'     => $trans->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop 'banking' (History)
        Schema::dropIfExists('banking');

        // Rename 'bank_config' back to 'banking'
        if (Schema::hasTable('bank_config')) {
            Schema::rename('bank_config', 'banking');
        }
    }
};
