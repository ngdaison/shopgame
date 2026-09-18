<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $payload = $request->validate([
            'page'      => 'nullable|integer',
            'limit'     => 'nullable|integer',
            'search'    => 'nullable|string',
            'sort_by'   => 'nullable|string',
            'sort_type' => 'nullable|string|in:asc,desc',
            'status'    => 'nullable|string',
            'category'  => 'nullable|string',
        ]);

        $query = Ticket::where('user_id', auth()->id());

        if (!empty($payload['search'])) {
            $search = $payload['search'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        
        if (isset($payload['status']) && $payload['status'] !== 'all') {
            $query->where('status', $payload['status']);
        }

        if (isset($payload['category']) && $payload['category'] !== 'all') {
            $query->where('category', $payload['category']);
        }

        $sortField = $payload['sort_by'] ?? 'id';
        $sortType = $payload['sort_type'] ?? 'desc';
        
        $query->orderBy($sortField, $sortType);

        $meta = [
          'page'       => (int) ($payload['page'] ?? 1),
          'limit'      => (int) ($payload['limit'] ?? 10),
          'total_rows' => $query->count(),
          'total_page' => ceil($query->count() / ($payload['limit'] ?? 10)),
        ];

        $data = $query->skip(($meta['page'] - 1) * $meta['limit'])->take($meta['limit'])->get();

        return response()->json([
            'data' => [
                'meta' => $meta,
                'data' => $data
            ],
            'status' => 200,
            'message' => 'Lấy danh sách thành công'
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
            'messages' => [$initialMessage] // Save directly
        ]);
        
        // Update ticket_id in message if we strictly needed it, but it's nested so implicit.
        // If we want consistency we could update it, but let's skip for overhead saving.

        // Gửi thông báo cho Admin
        $admins = User::where('role', 'admin')->get();
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
            'message' => 'Tạo yêu cầu thành công',
            'data' => $ticket
        ]);
    }

    public function show($id)
    {
        // $id here is actually the code passed from route if the frontend sends code
        // But route is resource? No, manual route.
        // If frontend sends code in URL, $id parameter receives code.
        $ticket = Ticket::where('user_id', auth()->id())
             ->where('code', $id) 
             ->firstOrFail();
            
        // Mark as read for user
        if ($ticket->unread_for_user > 0) {
            $ticket->update(['unread_for_user' => 0]);
        }
            
        // Hydrate sender info for messages
        // This is to maintain compatibility with "with('sender')"
        $messages = $ticket->messages ?? [];
        
        // Collect all sender IDs (users only presumably, or admins)
        $userIds = [];
        foreach ($messages as $msg) {
            $msgObj = (object)$msg;
            if (isset($msgObj->sender_id) && $msgObj->sender_id) {
                // If sender_type is user, lookup user. If admin, maybe generic admin user?
                // The API previously returned 'sender:id,username,fullname'.
                // Assuming admin uses same User table in this system (often case) or separate.
                // Based on migration `sender_id` is nullable.
                $userIds[] = $msgObj->sender_id;
            }
        }
        $userIds = array_unique($userIds);
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');
        
        $hydratedMessages = [];
        foreach ($messages as $msg) {
            $msgObj = (object)$msg;
            if (isset($msgObj->sender_id) && isset($users[$msgObj->sender_id])) {
                $user = $users[$msgObj->sender_id];
                $msgObj->sender = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'fullname' => $user->fullname ?? $user->name ?? $user->username // Fallback
                ];
            } else {
                 $msgObj->sender = null;
            }
            $hydratedMessages[] = $msgObj;
        }
        
        // Replace messages in ticket object strictly for response
        $ticket->setRelation('messages', collect($hydratedMessages)); 
        // Or just overwrite the attribute if allowed, but setRelation is cleaner for "with" emulation.
        // However, since `messages` is a casted attribute, accessing $ticket->messages returns the array.
        // We might need to wrap it in a structure that frontend expects if it expects a collection.
        // The original `with` returns a collection.
        // Let's just return $ticket and override the messages attribute in the array form.
        
        $ticketArray = $ticket->toArray();
        $ticketArray['messages'] = $hydratedMessages;

        return response()->json([
            'data' => $ticketArray
        ]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Use transaction to ensure atomic update and unique ID generation
        $newMessage = \Illuminate\Support\Facades\DB::transaction(function () use ($id, $request) {
            $ticket = Ticket::where('user_id', auth()->id())
                ->where('code', $id)
                ->lockForUpdate()
                ->firstOrFail();
            
            $newMessage = [
                'id' => (int)(microtime(true) * 1000), // Unique ID inside lock
                'ticket_id' => $ticket->id,
                'sender_type' => 'user',
                'sender_id' => auth()->id(),
                'message' => $request->message,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
            
            $messages = $ticket->messages ?? [];
            $messages[] = $newMessage;

            $ticket->messages = $messages;
            $ticket->last_message_at = now();
            $ticket->last_reply_by = 'user';
            // $ticket->unread_for_user = 0; // User read their own message
            $ticket->unread_for_admin = $ticket->unread_for_admin + 1;
            $ticket->status = 'open'; 
            $ticket->save();
            
            // Gửi thông báo cho Admin
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'type'    => 'ticket',
                    'title'   => 'Phản hồi hỗ trợ mới: ' . $ticket->title,
                    'content' => \Illuminate\Support\Str::limit($request->message, 100),
                    'link'    => route('admin.tickets.show', $ticket->id),
                    'is_read' => false
                ]);
            }
            
            return $newMessage;
        });

        return response()->json(['message' => 'Đã gửi phản hồi']);
    }
}
