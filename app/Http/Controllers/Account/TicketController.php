<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;

class TicketController extends Controller
{
    public function index()
    {
        return view('account.tickets.index', [
            'pageTitle' => 'Tickets / Hỗ trợ',
        ]);
    }

    public function show($code)
    {
        $ticket = Ticket::where('user_id', auth()->id())
            ->where('code', $code)
            ->firstOrFail();
        
        // Mark as read for user
        if ($ticket->unread_for_user > 0) {
            $ticket->update(['unread_for_user' => 0]);
        }

        // Mark related notifications as read
        \App\Models\Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->where(function($q) use ($code, $ticket) {
                $q->where('link', 'LIKE', '%/tickets/' . $code)
                  ->orWhere('link', 'LIKE', '%/tickets/' . $code . '%')
                  ->orWhere('link', 'LIKE', '%/admin/tickets/' . $ticket->id)
                  ->orWhere('link', 'LIKE', '%/admin/tickets/' . $ticket->id . '%');
            })
            ->update(['is_read' => true]);

        return view('account.tickets.show', [
            'pageTitle' => $ticket->title,
            'ticket' => $ticket,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'content' => 'required|string',
        ]);

        $initialMessage = [
            'id' => (int)(microtime(true) * 1000),
            'ticket_id' => null,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message' => $request->content,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'is_initial' => true
        ];

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
            'title' => $request->title,
            'category' => $request->category ?? 'General',
            'status' => 'open',
            'priority' => 0,
            'last_message_at' => now(),
            'last_reply_by' => 'user',
            'unread_for_admin' => 1,
            'messages' => [$initialMessage]
        ]);

        // Send notifications to admins
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'type'    => 'ticket',
                'title'   => 'Yêu cầu hỗ trợ mới: ' . $ticket->title,
                'content' => \Illuminate\Support\Str::limit($request->content, 100),
                'link'    => route('admin.tickets.show', $ticket->id),
                'is_read' => false
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Tạo yêu cầu thành công',
            'data' => $ticket
        ]);
    }
}
