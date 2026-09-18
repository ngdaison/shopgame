<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function index(Request $request)
  {
    $payload   = $request->validate([
      'page'      => 'nullable|integer|min:1',
      'limit'     => 'nullable|integer|min:1',
      'search'    => 'nullable|string|max:255',
      'sort_by'   => 'nullable|string|max:255',
      'sort_type' => 'nullable|string|in:asc,desc',
    ]);
    $page      = $payload['page'] ?? 1;
    $limit     = $payload['limit'] ?? 10;
    $search    = $payload['search'] ?? null;
    $offset    = ($page - 1) * $limit;
    $sort_by   = $payload['sort_by'] ?? 'id';
    $sort_type = $payload['sort_type'] ?? 'asc';

    $query = \App\Models\User::query();

    if ($payload['search'] ?? null) {
      $query->where(function ($query) use ($payload) {
        $query->where('id', 'like', '%' . $payload['search'] . '%')
          ->orWhere('username', 'like', '%' . $payload['search'] . '%')
          ->orWhere('full_name', 'like', '%' . $payload['search'] . '%')
          ->orWhere('phone', 'like', '%' . $payload['search'] . '%')
          ->orWhere('email', 'like', '%' . $payload['search'] . '%')
          ->orWhere('ip_address', 'like', '%' . $payload['search'] . '%');
      });
    }

    $total = $query->count();

    // Select users.* to ensure we get all base fields
    // Add subquery for the latest IP from history
    $query->select('users.*')
          ->addSelect(['last_history_ip' => \App\Models\History::select('ip_address')
              ->whereColumn('user_id', 'users.id')
              ->orderBy('id', 'desc')
              ->limit(1)
          ]);

    $data = $query->skip($offset)
      ->take($limit)
      ->orderBy($sort_by, $sort_type)
      ->get();

    // Override ip_address with waterfall fallback logic
    $data->transform(function($user) {
        $ip = $user->last_history_ip 
              ?? $user->last_login_ip 
              ?? $user->getRawOriginal('ip_address');
        
        // Clean up if it's JSON history
        if ($ip && is_string($ip) && (str_starts_with($ip, '{') || str_starts_with($ip, '['))) {
            try {
                $decoded = json_decode($ip, true);
                if (is_array($decoded)) {
                    $ip = end($decoded);
                }
            } catch (\Exception $e) {}
        }

        
        $user->setAttribute('is_online', \Illuminate\Support\Facades\Cache::has('user-is-online-' . $user->id));
        $user->setAttribute('ip_address', $ip ?: 'N/A');
        $user->setAttribute('max_level', $user->getRoleLevel());
        
        return $user;
    });

    return response()->json([
      'data'    => [
        'meta' => [
          'page'  => (int) $page,
          'total' => (int) $total,
          'limit' => (int) $limit,
        ],
        'data' => $data,
      ],
      'status'  => 200,
      'message' => 'Get data success',
    ]);
  }

  public function delete($id)
  {
    $user = \App\Models\User::find($id);
    if (!$user) {
      return response()->json(['status' => false, 'message' => 'Người dùng không tồn tại'], 404);
    }

    if ($user->id === auth()->id()) {
      return response()->json(['status' => false, 'message' => 'Bạn không thể tự xóa chính mình'], 400);
    }

    if (auth()->user()->getRoleLevel() < $user->getRoleLevel()) {
      return response()->json(['status' => false, 'message' => 'Bạn không có quyền xóa người dùng có cấp bậc cao hơn.'], 403);
    }

    $user->delete();

    return response()->json(['status' => true, 'message' => 'Xóa người dùng thành công']);
  }
}
