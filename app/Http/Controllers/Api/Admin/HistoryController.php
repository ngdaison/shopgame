<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
  public function index(Request $request)
  {
    $payload = $request->validate([
      'page' => 'nullable|integer|min:1',
      'limit' => 'nullable|integer|min:1',
      'search' => 'nullable|string|max:255',
      'sort_by' => 'nullable|string|max:255',
      'username' => 'nullable|string|max:255',
      'sort_type' => 'nullable|string|in:asc,desc',
    ]);
    $page = $payload['page'] ?? 1;
    $limit = $payload['limit'] ?? 10;
    $search = $payload['search'] ?? null;
    $offset = ($page - 1) * $limit;
    $sort_by = $payload['sort_by'] ?? 'id';
    $sort_type = $payload['sort_type'] ?? 'asc';

    $query = \App\Models\History::query()->with('user:id,username,domain,role');

    // Filter by Admin Hide
    $query->whereNull('admin_deleted_at');

    // Filter by Role Limit Date
    $limitDate = auth()->user()->getHistoryLimitDate();
    if ($limitDate) {
        $query->where('created_at', '>=', $limitDate);
    }

    if ($search) {
      $query->where('content', 'like', '%' . $search . '%')
        ->orWhere('ip_address', 'like', '%' . $search . '%');
    }

    if ($payload['username'] ?? null) {
      $query->where('username', $payload['username']);
    }

    $total = $query->count();

    $data = $query->skip($offset)
      ->take($limit)
      ->orderBy($sort_by, $sort_type)
      ->get();

    return response()->json([
      'data' => [
        'meta' => [
          'page' => (int)$page,
          'total' => (int)$total,
          'limit' => (int)$limit,
        ],
        'data' => $data,
      ],
      'status' => 200,
      'message' => 'Get data success',
    ]);
  }

  public function delete(Request $request)
  {
    if (auth()->user()->hasRole('Product Manager')) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền ẩn lịch sử!'
        ], 403);
    }

    $payload = $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'integer|exists:histories,id',
    ]);

    $protectedIds = \App\Models\History::whereIn('id', $payload['ids'])
      ->whereHas('user', function($q) {
          $q->where('role', 'like', '%Product Manager%');
      })
      ->pluck('id')
      ->toArray();

    $idsToDelete = array_diff($payload['ids'], $protectedIds);

    if (count($idsToDelete) > 0) {
      \App\Models\History::whereIn('id', $idsToDelete)->update([
        'admin_deleted_at' => now(),
      ]);
    }

    if (count($protectedIds) > 0) {
        return response()->json([
            'status' => 200,
            'message' => 'Đã ẩn ' . count($idsToDelete) . ' lịch sử. Không thể ẩn ' . count($protectedIds) . ' lịch sử của Product Manager!',
        ]);
    }

    return response()->json([
      'status' => 200,
      'message' => 'Lịch sử đã được ẩn thành công!',
    ]);
  }

  public function clearAll(Request $request)
  {
    if (auth()->user()->hasRole('Product Manager')) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền dọn dẹp lịch sử!'
        ], 403);
    }

    // Hide all histories except those belonging to Product Managers
    \App\Models\History::whereNull('admin_deleted_at')
      ->where(function ($query) {
          $query->whereDoesntHave('user')
              ->orWhereHas('user', function ($q) {
                  $q->where('role', 'not like', '%Product Manager%');
              });
      })
      ->update(['admin_deleted_at' => now()]);

    return response()->json([
      'status' => 200,
      'message' => 'Toàn bộ lịch sử đã được dọn dẹp thành công!',
    ]);
  }
}
