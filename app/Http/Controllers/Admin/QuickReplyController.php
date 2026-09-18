<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuickReply;
use Illuminate\Http\Request;

class QuickReplyController extends Controller
{
    public function index()
    {
        $replies = QuickReply::latest()->get();
        return response()->json([
            'status' => true,
            'data' => $replies
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'command' => 'required|string|unique:quick_replies,command',
            'content' => 'required|string',
        ]);

        $reply = QuickReply::create([
            'admin_id' => auth()->id(),
            'command' => $request->command,
            'content' => $request->content,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Quick reply created successfully',
            'data' => $reply
        ]);
    }

    public function update(Request $request, $id)
    {
        $reply = QuickReply::findOrFail($id);

        $request->validate([
            'command' => 'required|string|unique:quick_replies,command,' . $id,
            'content' => 'required|string',
        ]);

        $reply->update([
            'command' => $request->command,
            'content' => $request->content,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Quick reply updated successfully',
            'data' => $reply
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => 'required']);
        $reply = QuickReply::findOrFail($request->id);
        $reply->delete();

        return response()->json([
            'status' => true,
            'message' => 'Quick reply deleted successfully',
        ]);
    }
}
