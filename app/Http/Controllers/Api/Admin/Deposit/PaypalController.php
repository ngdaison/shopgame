<?php

namespace App\Http\Controllers\Api\Admin\Deposit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaypalController extends Controller
{
    public function index(Request $request)
    {
        $payload = $request->validate([
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:-1',
            'search' => 'nullable|sometimes',
            'sort_by' => 'nullable|string|max:255',
            'sort_type' => 'nullable|string|in:asc,desc',
            'draw' => 'nullable|integer',

            // Filters
            'username' => 'nullable|string',
            'member_id' => 'nullable|integer',
            'trans_id' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'status' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'domain' => 'nullable|string',
        ]);

        $page = $payload['page'] ?? 1;
        $limit = $payload['limit'] ?? 10;

        $searchInput = $request->input('search');
        $search = null;
        if (is_array($searchInput)) {
            $search = $searchInput['value'] ?? null;
        }
        else {
            $search = $searchInput;
        }

        $offset = ($page - 1) * $limit;
        if ($limit == -1) {
            $offset = 0;
            $limit = 999999999;
        }
        $sort_by = $payload['sort_by'] ?? 'transaction_id';
        $sort_type = $payload['sort_type'] ?? 'desc';

        if ($sort_by === 'user_domain' || $sort_by === 'domain') {
            $sort_by = 'paypal.domain';
        } elseif ($sort_by === 'id') {
            $sort_by = 'paypal.transaction_id';
        } elseif ($sort_by === 'code') {
            $sort_by = 'paypal.trans_id';
        } elseif ($sort_by === 'username') {
            $sort_by = 'paypal.username';
        } elseif ($sort_by === 'amount') {
            $sort_by = 'paypal.amount';
        } elseif ($sort_by === 'status') {
            $sort_by = 'paypal.status';
        } elseif ($sort_by === 'created_at') {
            $sort_by = 'paypal.created_at';
        } else {
            if (!str_contains($sort_by, '.')) {
                $sort_by = 'paypal.' . $sort_by;
            }
        }

        $query = \App\Models\Paypal::query()
            ->leftJoin('users', 'paypal.user_id', '=', 'users.id')
            ->select('paypal.*', 'users.domain as user_domain', 'users.role as user_role', 'paypal.transaction_id as id', 'paypal.trans_id as code');

        // Filter by Admin Hide
        $query->whereNull('paypal.admin_deleted_at');

        // Filter by Role Limit Date
        $limitDate = auth()->user()->getHistoryLimitDate();
        if ($limitDate) {
            $query->where('paypal.created_at', '>=', $limitDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('paypal.transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('paypal.trans_id', 'like', '%' . $search . '%')
                    ->orWhere('paypal.content', 'like', '%' . $search . '%')
                    ->orWhere('paypal.username', 'like', '%' . $search . '%');
            });
        }

        if (!empty($payload['username'])) {
            $query->where('paypal.username', 'like', '%' . $payload['username'] . '%');
        }

        if (!empty($payload['member_id'])) {
            $query->where('paypal.user_id', $payload['member_id']);
        }

        if (!empty($payload['trans_id'])) {
            $query->where('paypal.trans_id', 'like', '%' . $payload['trans_id'] . '%');
        }

        if (!empty($payload['amount'])) {
            $query->where('paypal.amount', $payload['amount']);
        }

        if (!empty($payload['status'])) {
            $query->where('paypal.status', $payload['status']);
        }

        if (!empty($payload['start_date']) && !empty($payload['end_date'])) {
            $query->whereBetween('paypal.created_at', [$payload['start_date'] . ' 00:00:00', $payload['end_date'] . ' 23:59:59']);
        } elseif (!empty($payload['start_date'])) {
            $query->where('paypal.created_at', '>=', $payload['start_date'] . ' 00:00:00');
        } elseif (!empty($payload['end_date'])) {
            $query->where('paypal.created_at', '<=', $payload['end_date'] . ' 23:59:59');
        }

        if (!empty($payload['domain'])) {
            $domain = $payload['domain'];
            $query->where(function ($q) use ($domain) {
                $q->where('paypal.domain', 'like', '%' . $domain . '%')
                    ->orWhereHas('user', function ($u) use ($domain) {
                        $u->where('domain', 'like', '%' . $domain . '%');
                    });
            });
        }

        try {
            $total = $query->count();
            $data = $query->skip($offset)
                ->take($limit)
                ->orderBy($sort_by, $sort_type)
                ->get();

            return response()->json([
                'draw' => (int)($payload['draw'] ?? 0),
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Paypal API Error: ' . $e->getMessage(), [
                'payload' => $payload,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 500,
                'message' => 'Lỗi máy chủ: ' . $e->getMessage(),
                'draw' => (int)($payload['draw'] ?? 0),
                'data' => ['data' => [], 'meta' => ['total' => 0]]
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền ẩn lịch sử nạp tiền!'
            ], 403);
        }

        $request->validate([
            'ids' => 'required|array',
        ]);

        $protectedIds = \App\Models\Paypal::whereIn('transaction_id', $request->ids)
            ->whereHas('user', function($q) {
                $q->where('role', 'like', '%Product Manager%');
            })
            ->pluck('transaction_id')
            ->toArray();
        
        $idsToDelete = array_diff($request->ids, $protectedIds);

        if (count($idsToDelete) > 0) {
            \App\Models\Paypal::whereIn('transaction_id', $idsToDelete)->update([
                'admin_deleted_at' => now(),
            ]);
        }

        if (count($protectedIds) > 0) {
            return response()->json([
                'status' => true,
                'message' => 'Đã ẩn ' . count($idsToDelete) . ' lịch sử. Không thể ẩn ' . count($protectedIds) . ' lịch sử của Product Manager!',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Lịch sử đã được ẩn thành công!',
        ]);
    }

    public function clearAll(Request $request)
  {
    if (auth()->user()->hasRole('Product Manager')) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền dọn dẹp lịch sử nạp tiền!'
        ], 403);
    }

    // Hide all Paypal except those belonging to Product Managers
    \App\Models\Paypal::whereNull('admin_deleted_at')
      ->where(function ($query) {
          $query->whereDoesntHave('user')
              ->orWhereHas('user', function ($q) {
                  $q->where('role', 'not like', '%Product Manager%');
              });
      })
      ->update(['admin_deleted_at' => now()]);

    return response()->json([
      'status' => true,
      'message' => 'Toàn bộ lịch sử nạp tiền đã được dọn dẹp thành công!',
    ]);
  }
}
