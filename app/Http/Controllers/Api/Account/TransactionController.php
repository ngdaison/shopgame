<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $payload = $request->validate([
            'page' => 'nullable|integer',
            'limit' => 'nullable|integer',
            'search' => 'nullable|string',
            'sort_by' => 'nullable|string',
            'sort_type' => 'nullable|string|in:asc,desc',
        ]);

        // If description_like is ATM, we assume it's a request for banking history
        if ($request->input('description_like') === 'ATM') {
            $query = \App\Models\Banking::where('user_id', auth()->user()->id);
            
            if (isset($payload['search'])) {
                $query = $query->where(function($q) use ($payload) {
                    $q->where('trans_id', 'like', '%'.$payload['search'].'%')
                      ->orWhere('content', 'like', '%'.$payload['search'].'%');
                });
            }
            
            // Map code to trans_id for frontend consistency if needed, 
            // but Banking model uses trans_id and Vue component expects code.
            // In BankList.vue: dataIndex: 'code'
        } else {
            $query = Transaction::where('user_id', auth()->user()->id);
            
            if (isset($payload['search'])) {
                $query = $query->where(function($q) use ($payload) {
                    $q->where('code', 'like', '%'.$payload['search'].'%')
                      ->orWhere('content', 'like', '%'.$payload['search'].'%');
                });
            }
        }

        if ($request->has('description_like') && $request->input('description_like') !== 'ATM') {
            $query = $query->where('content', 'like', '%' . $request->input('description_like') . '%');
        }

        if (isset($payload['sort_by'])) {
            // Map 'code' to 'trans_id' for Banking model sorting if sorted by 'code'
            $sortBy = $payload['sort_by'];
            if ($request->input('description_like') === 'ATM' && $sortBy === 'code') {
                $sortBy = 'trans_id';
            }
            $query = $query->orderBy($sortBy, $payload['sort_type'] ?? 'asc');
        } else {
            $query = $query->orderBy('id', 'desc');
        }

        $meta = [
            'page' => (int) ($payload['page'] ?? 1),
            'limit' => (int) ($payload['limit'] ?? 10),
            'total_rows' => $query->count(),
            'total_page' => ceil($query->count() / ($payload['limit'] ?? 10)),
        ];

        $raw_data = $query->skip(($meta['page'] - 1) * $meta['limit'])->take($meta['limit'])->get();
        
        // Transform for Banking model to match Transaction (code vs trans_id)
        if ($request->input('description_like') === 'ATM') {
            $data = $raw_data->map(function($item) {
                $item->code = $item->trans_id;
                return $item;
            });
        } else {
            $data = $raw_data;
        }

        return response()->json([
            'data' => [
                'meta' => $meta,
                'data' => $data,
            ],
            'status' => 200,
            'message' => 'Lấy danh sách giao dịch thành công',
        ], 200);

    }
}
