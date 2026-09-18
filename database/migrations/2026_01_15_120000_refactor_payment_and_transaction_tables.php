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
        // 1. Refactor 'bank_accounts' to 'banks'
        if (Schema::hasTable('bank_accounts')) {
            Schema::rename('bank_accounts', 'banks');
        }

        if (Schema::hasTable('banks')) {
            Schema::table('banks', function (Blueprint $table) {
                // Rename 'name' to 'bank_name'
                // Use Add-Copy-Drop for compatibility
                if (Schema::hasColumn('banks', 'name') && !Schema::hasColumn('banks', 'bank_name')) {
                    $table->string('bank_name')->after('id')->nullable();
                }
                
                // Add 'bank_code' if not exists. 'number' acts as account number.
                if (!Schema::hasColumn('banks', 'bank_code')) {
                    $table->string('bank_code')->nullable()->after('id');
                }

                // Add 'transaction_history' JSON column
                if (!Schema::hasColumn('banks', 'transaction_history')) {
                    $table->json('transaction_history')->nullable()->after('status');
                }
            });
            
            // Copy data and drop old column separate from schema change to avoid lock issues in some DBs or just for logic flow
            if (Schema::hasColumn('banks', 'name') && Schema::hasColumn('banks', 'bank_name')) {
                DB::statement('UPDATE banks SET bank_name = name');
            }

            Schema::table('banks', function (Blueprint $table) {
                if (Schema::hasColumn('banks', 'name') && Schema::hasColumn('banks', 'bank_name')) {
                     // Make bank_name not nullable after copy
                     $table->string('bank_name')->nullable(false)->change();
                     $table->dropColumn('name');
                }
            });
        }

        // 2. Refactor 'card_lists' to 'cards'
        if (Schema::hasTable('card_lists')) {
            Schema::rename('card_lists', 'cards');
        }

        if (Schema::hasTable('cards')) {
            Schema::table('cards', function (Blueprint $table) {
                // Ensure 'card_id' is the primary key (id is already primary)
                
                // Rename 'type' to 'card_type'
                if (Schema::hasColumn('cards', 'type') && !Schema::hasColumn('cards', 'card_type')) {
                     $table->string('card_type')->after('id')->nullable();
                }

                // Add 'expiry_date'
                if (!Schema::hasColumn('cards', 'expiry_date')) {
                    $table->date('expiry_date')->nullable()->after('card_type');
                }

                // Rename 'amount' to 'balance' logic
                if (!Schema::hasColumn('cards', 'balance')) {
                    $table->decimal('balance', 20, 2)->default(0)->after('card_type');
                }
            });

             // Copy data for cards
            if (Schema::hasColumn('cards', 'type') && Schema::hasColumn('cards', 'card_type')) {
                DB::statement('UPDATE cards SET card_type = type');
            }
            if (Schema::hasColumn('cards', 'amount') && Schema::hasColumn('cards', 'balance')) {
                 DB::statement('UPDATE cards SET balance = amount');
            }

            Schema::table('cards', function (Blueprint $table) {
                if (Schema::hasColumn('cards', 'type') && Schema::hasColumn('cards', 'card_type')) {
                     $table->string('card_type')->nullable(false)->change();
                     $table->dropColumn('type');
                }
                // If we want to replace amount with balance completely, or keep amount?
                // Request says "renamed from card_lists ... fields such as ... balance".
                // Usually 'amount' is face value. 'balance' is remaining.
                // I will keep 'amount' if it exists as it might be used, but if the user requested 'balance', I populated it.
                // I won't drop 'amount' as it might be critical for history, unless I'm sure.
                // But I'll leave 'amount' alone for now to avoid breaking too much, unless 'balance' *is* the amount.
                // Given the rename pattern, I'll assume they want 'balance' to BE the main field.
                // I'll drop 'amount' ? No, 'amount' in card lists usually is the denomination. 'balance' might be the same.
                // I'll leave 'amount' there but I've populated 'balance'. 
            });
        }

        // 3. Create 'usdt' table
        if (!Schema::hasTable('usdt')) {
            Schema::create('usdt', function (Blueprint $table) {
                $table->id('transaction_id'); // ID as transaction_id
                $table->unsignedBigInteger('user_id');
                $table->decimal('amount', 20, 2);
                $table->timestamp('transaction_date')->useCurrent();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        // 4. Create 'paypal' table
        if (!Schema::hasTable('paypal')) {
            Schema::create('paypal', function (Blueprint $table) {
                $table->id('transaction_id');
                $table->unsignedBigInteger('user_id');
                $table->string('paypal_email')->nullable();
                $table->decimal('amount', 20, 2);
                $table->timestamp('transaction_date')->useCurrent();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }

        // 5. Create 'perfect_money' table
        if (!Schema::hasTable('perfect_money')) {
            Schema::create('perfect_money', function (Blueprint $table) {
                $table->id('transaction_id');
                $table->unsignedBigInteger('user_id');
                $table->decimal('amount', 20, 2);
                $table->timestamp('transaction_date')->useCurrent();
                $table->string('status')->default('pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'banks' -> 'bank_accounts'
        if (Schema::hasTable('banks')) {
            Schema::table('banks', function (Blueprint $table) {
                if (Schema::hasColumn('banks', 'bank_name')) {
                    $table->renameColumn('bank_name', 'name');
                }
                $table->dropColumn(['bank_code', 'transaction_history']);
            });
            Schema::rename('banks', 'bank_accounts');
        }

        // Revert 'cards' -> 'card_lists'
        if (Schema::hasTable('cards')) {
            Schema::table('cards', function (Blueprint $table) {
                if (Schema::hasColumn('cards', 'card_type')) {
                    $table->renameColumn('card_type', 'type');
                }
                $table->dropColumn(['expiry_date', 'balance']);
            });
            Schema::rename('cards', 'card_lists');
        }

        Schema::dropIfExists('usdt');
        Schema::dropIfExists('paypal');
        Schema::dropIfExists('perfect_money');
    }
};
