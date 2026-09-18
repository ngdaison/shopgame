<?php

namespace App\Http\Controllers\Api\Admin\Deposit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banking;

class BankController extends Controller
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
            'bank' => 'nullable|string',
            'bank_code' => 'nullable|string',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'status' => 'nullable|string',
            'transfer_content' => 'nullable|string',
            'amount' => 'nullable|numeric',
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
        $sort_by = $payload['sort_by'] ?? 'created_at';
        $sort_type = $payload['sort_type'] ?? 'desc';

        if ($sort_by === 'user_domain' || $sort_by === 'domain') {
            $sort_by = 'domain';
        }

        $query = Banking::query()->with('user:id,username,domain,role');

        // Filter by Admin Hide
        $query->whereNull('admin_deleted_at');

        // Filter by Role Limit Date
        $limitDate = auth()->user()->getHistoryLimitDate();
        if ($limitDate) {
            $query->where('created_at', '>=', $limitDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('trans_id', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        if ($payload['username'] ?? null) {
            $query->where('username', 'like', '%' . $payload['username'] . '%');
        }

        if ($payload['member_id'] ?? null) {
            $query->where('user_id', $payload['member_id']);
        }

        if ($payload['trans_id'] ?? null) {
            $query->where('trans_id', 'like', '%' . $payload['trans_id'] . '%');
        }

        if ($payload['bank'] ?? null) {
            $query->where('content', 'like', '%' . $payload['bank'] . '%');
        }

        if ($payload['bank_code'] ?? null) {
            $query->where('bank_code', $payload['bank_code']);
        }

        if ($payload['transfer_content'] ?? null) {
            $query->where('content', 'like', '%' . $payload['transfer_content'] . '%');
        }

        if ($payload['amount'] ?? null) {
            $query->where('amount', $payload['amount']);
        }

        if ($payload['status'] ?? null) {
            $query->where('status', strtolower($payload['status']));
        }

        if (($payload['start_date'] ?? null) && ($payload['end_date'] ?? null)) {
            $query->whereBetween('created_at', [$payload['start_date'] . ' 00:00:00', $payload['end_date'] . ' 23:59:59']);
        } elseif ($payload['start_date'] ?? null) {
            $query->where('created_at', '>=', $payload['start_date'] . ' 00:00:00');
        } elseif ($payload['end_date'] ?? null) {
            $query->where('created_at', '<=', $payload['end_date'] . ' 23:59:59');
        }

        if (!empty($payload['domain'])) {
            $domain = $payload['domain'];
            $query->where(function ($q) use ($domain) {
                $q->where('domain', 'like', '%' . $domain . '%')
                    ->orWhereHas('user', function ($u) use ($domain) {
                        $u->where('domain', 'like', '%' . $domain . '%');
                    });
            });
        }

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
    }

    public function delete(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền ẩn lịch sử nạp tiền!'
            ], 403);
        }

        $payload = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:banking,id',
        ]);

        Banking::whereIn('id', $payload['ids'])->update([
            'admin_deleted_at' => now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Lịch sử nạp tiền đã được ẩn thành công!',
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

        // Hide all bankings except those belonging to Product Managers
        Banking::whereNull('admin_deleted_at')
            ->where(function ($query) {
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($q) {
                        $q->where('role', 'not like', '%Product Manager%');
                    });
            })
            ->update(['admin_deleted_at' => now()]);

        return response()->json([
            'status' => 200,
            'message' => 'Toàn bộ lịch sử nạp tiền đã được dọn dẹp thành công!',
        ]);
    }
}
