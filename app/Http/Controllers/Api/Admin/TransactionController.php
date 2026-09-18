<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
  public function index(Request $request)
  {
    $payload = $request->validate([
      'page' => 'nullable|integer|min:1',
      'type' => 'nullable|string',
      'limit' => 'nullable|integer|min:-1',
      'domain' => 'nullable|sometimes',
      'search' => 'nullable|sometimes',
      'sort_by' => 'nullable|string|max:255',
      'username' => 'nullable|string|max:255',
      'end_date' => 'nullable|date',
      'start_date' => 'nullable|date',
      'sort_type' => 'nullable|string|in:asc,desc',
      // New filters
      'member_id' => 'nullable|integer',
      'trans_id' => 'nullable|string|max:255',
      'bank' => 'nullable|string|max:255',
      'bank_code' => 'nullable|string|max:255',
      'transfer_content' => 'nullable|string|max:255',
      'code_prefix' => 'nullable|string|max:255',
      'status' => 'nullable|string',
      'draw' => 'nullable|integer',
      'amount' => 'nullable|numeric',
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
    $sort_by = $payload['sort_by'] ?? 'id';
    $sort_type = $payload['sort_type'] ?? 'desc';

    // Prefix sort_by to avoid ambiguity in joins
    if (!str_contains($sort_by, '.')) {
        if (in_array($sort_by, ['id', 'username', 'code', 'amount', 'status', 'created_at', 'content', 'balance_before', 'balance_after', 'bank_code', 'type'])) {
            $sort_by = 'transactions.' . $sort_by;
        } elseif ($sort_by === 'user_domain' || $sort_by === 'domain') {
            $sort_by = 'users.domain';
        }
    }

    // extras
    $domain = $payload['domain'] ?? null;
    $end_date = $payload['end_date'] ?? null;
    $start_date = $payload['start_date'] ?? null;

    $query = DB::table('transactions')
      ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
      ->select('transactions.*', 'users.domain as user_domain', 'users.role as user_role');

    // Filter by Admin Hide
    $query->whereNull('transactions.admin_deleted_at');

    // Filter by Role Limit Date
    $limitDate = auth()->user()->getHistoryLimitDate();
    if ($limitDate) {
        $query->where('transactions.created_at', '>=', $limitDate);
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('transactions.code', 'like', '%' . $search . '%')
          ->orWhere('transactions.content', 'like', '%' . $search . '%')
          ->orWhere('transactions.username', 'like', '%' . $search . '%');
      });
    }

    if ($payload['type'] ?? null) {
      if ($payload['type'] === 'deposit-bank' || $payload['type'] === 'banks') {
        $query->whereIn('transactions.type', ['deposit-bank', 'deposit-banking']);
      }
      elseif ($payload['type'] === 'cards' || $payload['type'] === 'deposit-card') {
        $query->where('transactions.type', 'deposit-card');
      }
      elseif ($payload['type'] === 'usdt') {
        $query->where(function ($q) {
          $q->where('transactions.type', 'usdt')->orWhere('transactions.code', 'like', 'FPM-%');
        });
      }
      elseif ($payload['type'] === 'paypal') {
        $query->where(function ($q) {
          $q->where('transactions.type', 'paypal')->orWhere('transactions.code', 'like', 'PPA-%');
        });
      }
      elseif ($payload['type'] === 'perfect_money') {
        $query->where('transactions.type', 'perfect_money');
      }
      else {
        $query->where('transactions.type', $payload['type']);
      }
    }

    // Specific Filters
    if ($payload['username'] ?? null) {
      $query->where('transactions.username', 'like', '%' . $payload['username'] . '%');
    }

    if ($payload['member_id'] ?? null) {
      $query->where('transactions.user_id', $payload['member_id']);
    }

    if ($payload['trans_id'] ?? null) {
      $query->where('transactions.code', 'like', '%' . $payload['trans_id'] . '%');
    }

    if ($payload['transfer_content'] ?? null) {
      $query->where('transactions.content', 'like', '%' . $payload['transfer_content'] . '%');
    }

    // "Bank" filter - searching in content for fallback or bank_code
    if ($payload['bank'] ?? null) {
      $query->where(function ($q) use ($payload) {
        $q->where('transactions.content', 'like', '%' . $payload['bank'] . '%');
      });
    }

    // Strict Bank Code Filter
    if ($payload['bank_code'] ?? null) {
      $query->where('transactions.bank_code', $payload['bank_code']);
    }

    if ($payload['code_prefix'] ?? null) {
      $query->where('transactions.code', 'like', $payload['code_prefix'] . '%');
    }

    if (!empty($payload['amount'])) {
      $query->where('transactions.amount', $payload['amount']);
    }

    if (!empty($start_date) && !empty($end_date)) {
      $query->whereBetween('transactions.created_at', [$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
    } elseif (!empty($start_date)) {
      $query->where('transactions.created_at', '>=', $start_date . ' 00:00:00');
    } elseif (!empty($end_date)) {
      $query->where('transactions.created_at', '<=', $end_date . ' 23:59:59');
    }

    if (!empty($domain)) {
      $query->where(function ($q) use ($domain) {
          $q->where('transactions.domain', 'like', '%' . $domain . '%')
            ->orWhere('users.domain', 'like', '%' . $domain . '%');
      });
    }

    if (!empty($payload['status'])) {
      $query->where('transactions.status', $payload['status']);
    }

    try {
      $total = $query->count();

      // Ensure sort_by is valid for the query
      $data = $query->skip($offset)
        ->take($limit)
        ->orderBy($sort_by, $sort_type)
        ->get();

      // Set domain_display: transaction.domain → joined user.domain → 'N/A'
      $data = $data->map(function ($transaction) {
        $transaction->domain_display = $transaction->domain
          ?? $transaction->user_domain
          ?? 'N/A';
        return $transaction;
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
    } catch (\Exception $e) {
      \Illuminate\Support\Facades\Log::error('TransactionController Error: ' . $e->getMessage(), [
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
            'message' => 'Bạn không có quyền ẩn giao dịch!'
        ], 403);
    }

    $request->validate([
      'ids' => 'required|array',
    ]);

    $protectedIds = DB::table('transactions')
      ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
      ->whereIn('transactions.id', $request->ids)
      ->where('users.role', 'like', '%Product Manager%')
      ->pluck('transactions.id')
      ->toArray();

    $idsToDelete = array_diff($request->ids, $protectedIds);

    if (count($idsToDelete) > 0) {
      DB::table('transactions')->whereIn('id', $idsToDelete)->update([
        'admin_deleted_at' => now(),
      ]);
    }

    if (count($protectedIds) > 0) {
        return response()->json([
            'status' => true,
            'message' => 'Đã ẩn ' . count($idsToDelete) . ' giao dịch. Không thể ẩn ' . count($protectedIds) . ' giao dịch của Product Manager!',
        ]);
    }

    return response()->json([
      'status' => true,
      'message' => 'Giao dịch đã được ẩn thành công!',
    ]);
  }

  public function clearAll(Request $request)
  {
    if (auth()->user()->hasRole('Product Manager')) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền dọn dẹp giao dịch!'
        ], 403);
    }

    // Hide all transactions except those belonging to Product Managers
    DB::table('transactions')
      ->whereNull('admin_deleted_at')
      ->whereNotExists(function ($query) {
          $query->select(DB::raw(1))
                ->from('users')
                ->whereRaw('users.id = transactions.user_id')
                ->where('users.role', 'like', '%Product Manager%');
      })
      ->update(['admin_deleted_at' => now()]);

    return response()->json([
      'status' => true,
      'message' => 'Toàn bộ giao dịch đã được dọn dẹp thành công!',
    ]);
  }
}
