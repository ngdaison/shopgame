<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;

class InvoiceController extends Controller
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
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'username' => 'nullable|string',
            'member_id' => 'nullable|integer',
            'code' => 'nullable|string', // trans_id in view usually maps to code or trans_id
            'amount' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $page = $payload['page'] ?? 1;
        $limit = $payload['limit'] ?? 10;

        // Handle search parameter which can be a string or an object (DataTable)
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

        // Prefix sort_by to avoid ambiguity in joins
        if (!str_contains($sort_by, '.')) {
            if (in_array($sort_by, ['id', 'username', 'code', 'amount', 'status', 'created_at', 'type', 'updated_at'])) {
                $sort_by = 'invoices.' . $sort_by;
            } elseif ($sort_by === 'user_domain') {
                $sort_by = 'users.domain';
            }
        }

        $query = Invoice::query()
            ->leftJoin('users', 'invoices.user_id', '=', 'users.id')
            ->select('invoices.*', 'users.domain as user_domain');

        // Filter by Admin Hide
        $query->whereNull('invoices.admin_deleted_at');

        // Filter by Role Limit Date
        $limitDate = auth()->user()->getHistoryLimitDate();
        if ($limitDate) {
            $query->where('invoices.created_at', '>=', $limitDate);
        }

        if ($payload['search'] ?? null) {
            $search = $payload['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        if ($payload['type'] ?? null) {
            $query->where('type', $payload['type']);
        }

        if ($payload['status'] ?? null) {
            $query->whereRaw('LOWER(status) = ?', [strtolower($payload['status'])]);
        }

        if ($payload['username'] ?? null) {
            $query->where('username', 'like', '%' . $payload['username'] . '%');
        }

        if ($payload['member_id'] ?? null) {
            $query->where('user_id', $payload['member_id']);
        }

        if ($payload['code'] ?? null) {
            $query->where('code', 'like', '%' . $payload['code'] . '%');
        }

        if ($payload['amount'] ?? null) {
            $query->where('amount', $payload['amount']);
        }

        if (($payload['start_date'] ?? null) && ($payload['end_date'] ?? null)) {
            $query->whereBetween('invoices.created_at', [$payload['start_date'] . ' 00:00:00', $payload['end_date'] . ' 23:59:59']);
        } elseif ($payload['start_date'] ?? null) {
            $query->where('invoices.created_at', '>=', $payload['start_date'] . ' 00:00:00');
        } elseif ($payload['end_date'] ?? null) {
            $query->where('invoices.created_at', '<=', $payload['end_date'] . ' 23:59:59');
        }

        $total = $query->count();

        $data = $query->skip($offset)
            ->take($limit)
            ->orderBy($sort_by, $sort_type)
            ->get();


        // Set domain_display: invoice.domain → joined user.domain → 'N/A'
        $data = $data->map(function ($item) {
            $item->domain_display = $item->domain ?? $item->user_domain ?? 'N/A';
            return $item;
        });

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
                'message' => 'Bạn không có quyền ẩn hoá đơn!'
            ], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:invoices,id',
        ]);

        $protectedIds = Invoice::whereIn('id', $request->ids)
            ->whereHas('user', function($q) {
                $q->where('role', 'like', '%Product Manager%');
            })
            ->pluck('id')
            ->toArray();
        
        $idsToDelete = array_diff($request->ids, $protectedIds);

        if (count($idsToDelete) > 0) {
            Invoice::whereIn('id', $idsToDelete)->update([
                'admin_deleted_at' => now(),
            ]);
        }

        if (count($protectedIds) > 0) {
            return response()->json([
                'status' => true,
                'message' => 'Đã ẩn ' . count($idsToDelete) . ' hoá đơn. Không thể ẩn ' . count($protectedIds) . ' hoá đơn của Product Manager!',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Hoá đơn đã được ẩn thành công!',
        ]);
    }

    public function clearAll(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền dọn dẹp hoá đơn!'
            ], 403);
        }

        // Hide all invoices except those belonging to Product Managers
        Invoice::whereNull('admin_deleted_at')
            ->where(function ($query) {
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($q) {
                        $q->where('role', 'not like', '%Product Manager%');
                    });
            })
            ->update(['admin_deleted_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Toàn bộ hoá đơn đã được dọn dẹp thành công!',
        ]);
    }
}
