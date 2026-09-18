<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $users = User::select('id', 'username')->orderBy('username', 'asc')->get();

        return view('admin.notifications.index', compact('notifications', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'usernames'    => 'nullable|array',
            'category_ids' => 'nullable|array',
            'title'        => 'required|string',
            'subtitle'     => 'nullable|string',
            'body'         => 'nullable|string',
            'link'         => 'nullable|string',
        ]);

        $userIds = collect();

        // Target Specific Users
        if ($request->filled('usernames')) {
            $foundUserIds = User::whereIn('username', $request->usernames)->pluck('id');
            $userIds = $userIds->merge($foundUserIds);
        }

        // Target by Categories (Removed as requested)
        
        // All Users if nothing selected
        if (!$request->filled('usernames')) {
            $userIds = User::pluck('id');
        }

        $userIds = $userIds->unique();

        if ($userIds->isEmpty()) {
            return redirect()->back()->with('error', 'No users found to send notification.');
        }

        // Send Notifications
        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type'    => 'system',
                'title'   => $request->title,
                'content' => $request->subtitle,
                'body'    => $request->body,
                'link'    => $request->link,
                'icon'    => 'fa fa-bell',
            ]);
        }

        return redirect()->back()->with('success', 'Sent notification to ' . $userIds->count() . ' users.');
    }

    public function edit($id)
    {
        $notification = Notification::findOrFail($id);
        $users = User::select('id', 'username')->orderBy('username', 'asc')->get();
        return view('admin.notifications.edit', compact('notification', 'users'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id'       => 'required|exists:notifications,id',
            'title'    => 'required|string',
            'subtitle' => 'nullable|string',
            'body'     => 'nullable|string',
            'link'     => 'nullable|string',
        ]);

        $notification = Notification::findOrFail($request->id);
        $notification->update([
            'title'   => $request->title,
            'content' => $request->subtitle,
            'body'    => $request->body,
            'link'    => $request->link,
        ]);

        return redirect()->route('admin.notifications.index')->with('success', 'Cập nhật thành công');
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
        Notification::destroy($id);
        return redirect()->back()->with('success', 'Deleted successfully');
    }
}
