<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
  public function index(Request $request)
  {
    $payload = $request->validate([
      'page'      => 'nullable|integer',
      'limit'     => 'nullable|integer',
      'search'    => 'nullable|string',
      'sort_by'   => 'nullable|string',
      'sort_type' => 'nullable|string|in:asc,desc',
      'type'      => 'nullable|string',
    ]);

    $type = $payload['type'] ?? null;
    $query = null;

    if ($type === 'paypal') {
        $query = \App\Models\Paypal::where('user_id', $request->user()->id)->select('*', 'trans_id as code');
    } elseif ($type === 'fpayment' || $type === 'fpayament') {
        $query = \App\Models\Usdt::where('user_id', $request->user()->id)->select('*', 'trans_id as code');
    } elseif ($type === 'perfect_money') {
        $query = \App\Models\PerfectMoney::where('user_id', $request->user()->id)->select('*', 'trans_id as code');
    } else {
        $query = Invoice::where('user_id', $request->user()->id);
        if ($type) {
            $query->where('type', $type);
        }
    }

    if (isset($payload['search'])) {
        $search = $payload['search'];
        $query->where(function($q) use ($search) {
            $q->where('content', 'like', '%' . $search . '%');
            // Check if trans_id or code exists in this model's schema
            if (isset($q->getModel()->getFillable()['trans_id']) || \Schema::hasColumn($q->getModel()->getTable(), 'trans_id')) {
                $q->orWhere('trans_id', 'like', '%' . $search . '%');
            } else {
                $q->orWhere('code', 'like', '%' . $search . '%');
            }
        });
    }

    if (isset($payload['sort_by'])) {
      $sort_by = $payload['sort_by'];
      // Mapping if needed, e.g. if sorting by 'code' but model has 'trans_id'
      if ($sort_by === 'code' && $type !== null && $type !== 'invoices') {
          $sort_by = 'trans_id';
      }
      $query->orderBy($sort_by, $payload['sort_type'] ?? 'asc');
    }

    $meta = [
      'page'       => (int) ($payload['page'] ?? 1),
      'limit'      => (int) ($payload['limit'] ?? 10),
      'total_rows' => $query->count(),
      'total_page' => ceil($query->count() / ($payload['limit'] ?? 10)),
    ];

    $data = $query->skip(($meta['page'] - 1) * $meta['limit'])->take($meta['limit'])->get();

    if ($data->isEmpty()) {
      return response()->json([
        'data'    => [
          'meta' => $meta,
          'data' => $data,
        ],
        'status'  => 204,
        'message' => 'Không có hoạt động nào',
      ], 204);
    }

    return response()->json([
      'data'    => [
        'meta' => $meta,
        'data' => $data,
      ],
      'status'  => 200,
      'message' => 'Lấy danh sách hoạt động thành công',
    ], 200);

  }

  public function show(Request $request, $id)
  {
    $invoice = Invoice::where('user_id', $request->user()->id)->findOrFail($id);

    return response()->json([
      'data'    => $invoice->makeVisible('payment_details'),
      'status'  => 200,
      'message' => 'Lấy thông tin hóa đơn thành công',
    ], 200);
  }


}
