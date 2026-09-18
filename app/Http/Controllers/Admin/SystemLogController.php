<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SystemLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemLog::query();

        // Filter by Admin Hide
        $query->whereNull('admin_deleted_at');

        // Filter by Role Limit Date
        $limitDate = auth()->user()->getHistoryLimitDate();
        if ($limitDate) {
            $query->where('created_at', '>=', $limitDate);
        }

        // Join with user to search by username if needed (optional, but good for display)
        $query->with('user');

        // Filter by User ID
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by Method
        if ($request->filled('method')) {
            $query->where('method', $request->input('method'));
        }

        // Filter by Request URL
        if ($request->filled('request_url')) {
            $query->where('request_url', 'like', '%' . $request->input('request_url') . '%');
        }

        // Filter by IP
        if ($request->filled('ip')) {
            $query->where('ip', 'like', '%' . $request->input('ip') . '%');
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.logs.index', compact('logs'));
    }

    public function delete(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền ẩn nhật ký!'
            ], 403);
        }

        $request->validate([
            'id' => 'required|integer|exists:system_logs,id'
        ]);

        $log = SystemLog::with('user')->find($request->id);
        
        if ($log && $log->user && str_contains($log->user->role, 'Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể ẩn nhật ký của Product Manager!'
            ], 403);
        }

        SystemLog::where('id', $request->id)->update([
            'admin_deleted_at' => now()
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Nhật ký đã được ẩn thành công!'
        ]);
    }

    public function clearAll(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền dọn dẹp nhật ký!'
            ], 403);
        }

        // Hide all logs except those belonging to Product Managers
        SystemLog::whereNull('admin_deleted_at')
            ->where(function ($query) {
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($q) {
                        $q->where('role', 'not like', '%Product Manager%');
                    });
            })
            ->update(['admin_deleted_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Toàn bộ nhật ký đã được dọn dẹp thành công!'
        ]);
    }
}
