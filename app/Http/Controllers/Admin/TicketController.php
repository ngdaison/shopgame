<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index()
    {
        return view('admin.tickets.index');
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'content'  => 'required|string',
        ]);

        $initialMessage = [
            'id' => (int)(microtime(true) * 1000),
            'ticket_id' => null,
            'sender_type' => 'admin',
            'admin_id' => auth()->id(),
            'message' => $payload['content'],
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ];

        $ticket = Ticket::create([
            'user_id' => $payload['user_id'],
            'code' => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10)),
            'title' => $payload['title'],
            'category' => $payload['category'] ?? 'General',
            'status' => 'open',
            'priority' => 0,
            'last_message_at' => now(),
            'last_reply_by' => 'admin',
            'unread_for_user' => 1,
            'unread_for_admin' => 0,
            'messages' => [$initialMessage]
        ]);

        // Gửi thông báo cho người dùng
        \App\Models\Notification::create([
            'user_id' => $ticket->user_id,
            'type'    => 'ticket',
            'title'   => 'Hỗ trợ mới: ' . $ticket->title,
            'content' => \Illuminate\Support\Str::limit($payload['content'], 100),
            'icon'    => 'fa fa-ticket-alt text-primary',
            'link'    => route('account.tickets.show', $ticket->code),
            'is_read' => false
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo ticket thành công',
            'data' => $ticket
        ]);
    }

    public function getList(Request $request)
    {
        $status = $request->get('status', 'open'); // 'open', 'closed'

        $query = Ticket::with(['user'])
            ->select('tickets.*')
            ->selectRaw("(CASE WHEN status != 'closed' AND last_reply_by='user' THEN 1 ELSE 0 END) as is_waiting");

        if ($status === 'closed') {
             $query->where('status', 'closed')->limit(50);
        } else {
             $query->where('status', '!=', 'closed');
        }

        $tickets = $query->orderByDesc('is_waiting')
            ->orderByDesc('last_message_at')
            ->get();
            
        // Map for easier frontend consumption
        $data = $tickets->map(function($t) {
            $lastMsg = $t->latest_message; // Accessor
            return [
                'id' => $t->id,
                'title' => $t->title,
                'category' => $t->category,
                'status' => $t->status,
                'user' => [
                    'username' => optional($t->user)->username ?? 'Unknown',
                    'avatar' => !empty($t->user->avatar) ? $t->user->avatar : asset('/images/avatar/av-1.svg'),
                    'is_online' => optional($t->user)->id ? \Illuminate\Support\Facades\Cache::has('user-is-online-' . $t->user->id) : false,
                ],
                'last_message' => $lastMsg ? Str::limit($lastMsg->message, 40) : 'No messages',
                'updated_at_human' => $t->updated_at->diffForHumans(),
                'last_message_at_human' => $t->last_message_at ? $t->last_message_at->diffForHumans() : '',
                'unread_for_admin' => $t->unread_for_admin,
                'is_waiting' => $t->is_waiting,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
            'counts' => [
                'open' => Ticket::where('status', '!=', 'closed')->count(),
                'closed' => Ticket::where('status', 'closed')->count(),
            ]
        ]);
    }

    public function getTicketContent($id)
    {
        $ticket = Ticket::with(['user'])->findOrFail($id);
        
        // Mark as read
        if ($ticket->unread_for_admin > 0) {
            $ticket->update(['unread_for_admin' => 0]);
        }
        
        // Fetch latest messages (Initial load: 30)
        // From JSON array
        $allMessages = $ticket->messages ?? [];
        
        // Mark initial message
        if (isset($allMessages[0])) {
            // Need to convert to array if object, but casts 'array' gives array of arrays usually if derived from JSON
            // If it's array of objects, we need to be careful.
            // Let's force array access.
            if (is_array($allMessages[0])) {
                $allMessages[0]['is_initial'] = true;
            } else {
                 // If for some reason it's object
                 $allMessages[0]->is_initial = true;
            }
        }
        
        // Sort descending by id/timestamp if needed, but assuming stored in append order (asc)
        // Reverse to get latest first for take logic if we want "latest 30"
        $reversed = array_reverse($allMessages);
        $slice = array_slice($reversed, 0, 30);
        
        $messages = collect($slice)->reverse()->values()->map(function($m) {
            // Need to handle object/array since JSON decode might be assoc or object depending on cast
            // Laravel 'array' cast returns array
            $m = (object)$m; 
            $created_at = \Carbon\Carbon::parse($m->created_at);
            $m->time_str = $created_at->format('H:i d/m/Y');
            return $m;
        });

        $infoHtml = view('admin.tickets.partials.info', compact('ticket'))->render();
        
        // Pass basic ticket info for Header update
        return response()->json([
            'status' => true,
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'user' => [
                    'username' => $ticket->user->username ?? 'Unknown',
                    'avatar' => !empty($ticket->user->avatar) ? $ticket->user->avatar : asset('/images/avatar/av-1.svg'),
                    'is_online' => $ticket->user ? \Illuminate\Support\Facades\Cache::has('user-is-online-' . $ticket->user->id) : false,
                ]
            ],
            'info_html' => $infoHtml,
            'messages' => $messages
        ]);
    }
    
    public function show($id) {
        // Redirect to index with ticket ID selected if accessed directly
        return redirect()->route('admin.tickets', ['id' => $id]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate(['message' => 'required']);
        
        // Use Transaction to prevent race condition
        $newMessage = \Illuminate\Support\Facades\DB::transaction(function () use ($id, $request) {
             $ticket = Ticket::lockForUpdate()->findOrFail($id);
             
             $newMessage = [
                'id' => (int)(microtime(true) * 1000), // Unique ID in milliseconds
                'ticket_id' => $ticket->id,
                'user_id' => null, // Admin
                'admin_id' => auth()->id() ?? 1,
                'sender_type' => 'admin',
                'message' => $request->message,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];

             $messages = $ticket->messages ?? [];
             $messages[] = $newMessage;

             $ticket->messages = $messages;
             $ticket->last_message_at = now();
             $ticket->last_reply_by = 'admin';
             $ticket->unread_for_user = $ticket->unread_for_user + 1;
             $ticket->status = 'open';
             $ticket->save();
             
             // Gửi thông báo cho người dùng
             \App\Models\Notification::create([
                 'user_id' => $ticket->user_id,
                 'type'    => 'ticket',
                 'title'   => 'Phản hồi hỗ trợ mới',
                 'content' => \Illuminate\Support\Str::limit($request->message, 100),
                 'icon'    => 'fa fa-reply text-info',
                 'link'    => route('account.tickets.show', $ticket->code),
                 'is_read' => false
             ]);

             return $newMessage;
        });

        $respMsg = (object)$newMessage;
        $respMsg->time_str = \Carbon\Carbon::parse($respMsg->created_at)->format('H:i d/m/Y');

        return response()->json([
            'status' => true,
            'data' => $respMsg
        ]);
    }

    public function status(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,closed',
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => $request->status]);

        return response()->json([
            'status' => true,
            'message' => 'Updated status',
        ]);
    }
    
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        
        $ticket = Ticket::findOrFail($request->id);
        $ticket->delete(); 
        
        return response()->json([
            'status' => true,
            'message' => 'Ticket deleted',
        ]);
    }

    public function getMessages(Request $request, $id)
    {
        $limit = $request->get('limit', 20);
        $beforeId = $request->get('before_id');

        $ticket = Ticket::findOrFail($id);
        $allMessages = $ticket->messages ?? [];
        
        // Mark initial message
        if (isset($allMessages[0])) {
            if (is_array($allMessages[0])) {
                $allMessages[0]['is_initial'] = true;
            } else {
                 $allMessages[0]->is_initial = true;
            }
        }
        
        // Filter if beforeId exists
        if ($beforeId) {
             $allMessages = array_filter($allMessages, function($m) use ($beforeId) {
                 return ((object)$m)->id < $beforeId;
             });
        }
        
        // Get latest of the filtered
        // Assuming array is sorted ASC by creation.
        // We want latest $limit.
        $slice = array_slice($allMessages, -$limit);
        
        $messages = collect($slice)->values()->map(function($msg) {
             $msg = (object)$msg;
                return [
                    'id' => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'message' => $msg->message,
                    'created_at' => $msg->created_at,
                    'time_str' => \Carbon\Carbon::parse($msg->created_at)->format('H:i d/m/Y'),
                    'sender' => null, // Sender info refactor needed if frontend expects it strictly
                    'is_initial' => $msg->is_initial ?? false
                ];
            });
            
        // Reset unread if polling current active
        if ($ticket->unread_for_admin > 0) {
            $ticket->update(['unread_for_admin' => 0]);
        }

        return response()->json([
            'status' => true,
            'data' => $messages,
            'ticket_status' => $ticket->status
        ]);
    }
    public function updateNote(Request $request, $id)
    {
        $request->validate(['note' => 'nullable|string']);
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['admin_note' => $request->note]);
        return response()->json(['status' => true, 'message' => 'Note updated']);
    }
}
