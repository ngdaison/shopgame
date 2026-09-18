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
        // 1. Add messages column
        // 1. Add messages column
        if (!Schema::hasColumn('tickets', 'messages')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->longText('messages')->nullable()->after('order_code'); 
                // Using longText to store JSON to ensure compatibility
            });
        }

        // 2. Migrate Data
        $tickets = DB::table('tickets')->get();
        foreach ($tickets as $ticket) {
            $oldMessages = DB::table('ticket_messages')
                ->where('ticket_id', $ticket->id)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $messagesArray = [];
            foreach ($oldMessages as $msg) {
                $messagesArray[] = [
                    'id' => $msg->id, // Validate if we want to keep old ID or generate new ones. Keeping old ID for reference is fine.
                    'sender_type' => $msg->sender_type,
                    'sender_id' => $msg->sender_id,
                    'message' => $msg->message,
                    'created_at' => $msg->created_at,
                    'updated_at' => $msg->updated_at,
                ];
            }

            DB::table('tickets')
                ->where('id', $ticket->id)
                ->update(['messages' => json_encode($messagesArray)]);
        }

        // 3. Drop old table
        Schema::dropIfExists('ticket_messages');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recreate table
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id')->index();
            $table->string('sender_type', 10); // user | admin
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->longText('message');
            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });

        // 2. Restore data
        $tickets = DB::table('tickets')->get();
        foreach ($tickets as $ticket) {
            if (empty($ticket->messages)) continue;
            
            $messages = json_decode($ticket->messages, true);
            if (!is_array($messages)) continue;

            foreach ($messages as $msg) {
                DB::table('ticket_messages')->insert([
                    'ticket_id' => $ticket->id,
                    'sender_type' => $msg['sender_type'] ?? 'user',
                    'sender_id' => $msg['sender_id'] ?? null,
                    'message' => $msg['message'] ?? '',
                    'created_at' => $msg['created_at'] ?? now(),
                    'updated_at' => $msg['updated_at'] ?? now(),
                ]);
            }
        }

        // 3. Drop column
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('messages');
        });
    }
};
