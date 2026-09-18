<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        // Mark all as read when visiting the page? Or just list? 
        // User usually wants to see unread status. We won't auto-read here.

        // Check if there are any unread notifications
        $hasUnread = Notification::where('user_id', Auth::id())->where('is_read', false)->exists();
        
        // Get the Title from domain config (falls back to general settings)
        $appTitle = getAppTitleWithFallback();

        return view('account.notifications.index', compact('notifications', 'hasUnread', 'appTitle'));
    }

    public function show($code)
    {
        $notification = Notification::where('user_id', Auth::id())->where('code', $code)->firstOrFail();
        
        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }
        
        // Get the Title from domain config (falls back to general settings)
        $appTitle = getAppTitleWithFallback();

        return view('account.notifications.show', compact('notification', 'appTitle'));
    }


    public function readAll(\Illuminate\Http\Request $request)
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => true]);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Đã đánh dấu tất cả là đã đọc.']);
        }

        return redirect()->back()->with('success', 'Đã đánh dấu tất cả là đã đọc.');
    }
}
