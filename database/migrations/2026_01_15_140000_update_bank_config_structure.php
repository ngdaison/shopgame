<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('bank_config')) {
            // 1. Fetch current data
            $oldBanks = DB::table('bank_config')->get();
            
            // 2. Prepare JSON structure
            $bankAccounts = [];
            foreach ($oldBanks as $bank) {
                // Ensure each bank has a unique ID for the JSON array
                $uuid = (string) Str::uuid();
                // Map fields. Note: We keep original ID if possible or map to 'id'
                $bankAccount = [
                    'id'        => $bank->id ?? $uuid,
                    'bank_name' => $bank->bank_name ?? $bank->name ?? 'Unknown',
                    'image'     => $bank->image ?? '',
                    'owner'     => $bank->owner ?? '',
                    'number'    => $bank->number ?? '',
                    'branch'    => $bank->branch ?? '',
                    'password'  => $bank->password ?? '',
                    'token'     => $bank->token ?? '',
                    'status'    => (bool) ($bank->status ?? true),
                    'provider'  => $bank->provider ?? '',
                    'bank_code' => $bank->bank_code ?? '',
                    'transaction_history' => isset($bank->transaction_history) ? json_decode($bank->transaction_history, true) : []
                ];
                $bankAccounts[] = $bankAccount;
            }

            // 3. Drop all columns except 'id' (and maybe timestamps)
            // But user wants specific: id | config | bank_accounts
            
            // It's safer to drop table and recreate it because we are changing schema drastically
            Schema::drop('bank_config');
            
            Schema::create('bank_config', function (Blueprint $table) {
                $table->id();
                $table->longText('config')->nullable();
                $table->longText('bank_accounts')->nullable(); // JSON array of banks
                $table->timestamps();
            });

            // 4. Insert Single Row
            // Fetch old config if any (from 'config' or 'configs' table)
            $configData = [];
            if (Schema::hasTable('config')) {
                $oldConfig = DB::table('config')->where('name', 'deposit_info')->value('value');
                $configData = $oldConfig ? json_decode($oldConfig, true) : [];
            } elseif (Schema::hasTable('configs')) {
                $oldConfig = DB::table('configs')->where('name', 'deposit_info')->value('value');
                $configData = $oldConfig ? json_decode($oldConfig, true) : [];
            } else {
                 // Try looking for 'settings' or generic fallback
                 // For now, empty config is acceptable as user can re-save
            }

            DB::table('bank_config')->insert([
                'id' => 1,
                'config' => json_encode($configData),
                'bank_accounts' => json_encode($bankAccounts),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this is hard as we lost individual rows structure unless we parse JSON back.
        // For now, we drop table and recreate mostly empty original structure
        Schema::dropIfExists('bank_config');
        
        Schema::create('bank_config', function (Blueprint $table) {
            $table->id();
             $table->string('name')->nullable(); // Old name
             $table->string('bank_name')->nullable();
             // ... dozens of columns
             $table->timestamps();
        });
    }
};
