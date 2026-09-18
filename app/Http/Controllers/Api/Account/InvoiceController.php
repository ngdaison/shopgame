<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
    ]);

    $query = Invoice::where('user_id', $request->user()->id);

    if (isset($payload['search'])) {
      $query = $query->where('content', 'like', '%' . $payload['search'] . '%')
        ->orWhere('ip_address', 'like', '%' . $payload['search'] . '%');
    }

    if (isset($payload['sort_by'])) {
      $query = $query->orderBy($payload['sort_by'], $payload['sort_type'] ?? 'asc');
    }

    $meta = [
      'page'  => (int) ($payload['page'] ?? 1),
      'limit' => (int) ($payload['limit'] ?? 10),
      'total' => $query->count(),
    ];

    $data = $query->skip(($meta['page'] - 1) * $meta['limit'])->take($meta['limit']);

    return response()->json([
      'data'    => [
        'meta' => $meta,
        'data' => $data->get(),
      ],
      'status'  => 200,
      'message' => 'Lấy danh sách hoạt động thành công',
    ], 200);

  }

  public function show(Request $request, $id)
  {
    $invoice = Invoice::where('user_id', $request->user()->id)->findOrFail($id);

    return response()->json([
      'data'    => $invoice,
      'status'  => 200,
      'message' => 'Lấy thông tin hóa đơn thành công',
    ], 200);
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'amount'  => 'required|integer|min:1',
      'channel' => 'required|string|in:perfect_money,fpayment',
    ]);

    $user   = $request->user();
    $amount = $payload['amount'];
    $config = Helper::getApiConfig($payload['channel']);


    if ($payload['channel'] === 'fpayment') {
      if (!isset($config['address_wallet'])) {
        return response()->json([
          'status'  => 400,
          'message' => 'Chưa cấu hình ví tiền điện tử',
        ], 400);
      }

      $invoiceCount = Invoice::where('user_id', $user->id)->where('status', 'processing')->where('type', 'fpayament')->count();
      if ($invoiceCount > 3) {
        return response()->json([
          'status'  => 400,
          'message' => 'Bạn đã có 3 hóa đơn đang chờ xử lý, vui lòng chờ xử lý hoặc hủy bỏ hóa đơn cũ trước khi tạo hóa đơn mới',
        ], 400);
      }

      $code      = 'BMX-' . Helper::randomString(7, true);
      $requestId = Helper::randomString(10);

      $createInvoice = Http::get('https://fpayment.co/api/AddInvoice.php', [
        'token_wallet'   => $config['token_wallet'],
        'address_wallet' => $config['address_wallet'],
        'name'           => 'Nạp Tiền - ' . $user->username,
        'description'    => 'mã giao dịch ' . $code,
        'amount'         => $amount,
        'request_id'     => $requestId,
        'callback'       => route('cron.deposit.fpayment-callback'),
        'return_url'     => route('account.deposits.crypto')
      ]);

      if ($createInvoice->failed()) {
        return response()->json([
          'status'  => 500,
          'message' => 'Lỗi máy chủ, vui lòng liên hệ admin!',
        ], 500);
      }

      $result = $createInvoice->json();

      if (isset($result['status']) && $result['status'] !== 'success') {
        return response()->json([
          'status'  => 500,
          'message' => $result['msg'] ?? 'Lỗi máy chủ, vui lòng liên hệ admin!',
        ], 500);
      }

      $data = $result['data'];

      $invoice = Invoice::create([
        'code'            => $code,
        'type'            => 'fpayament',
        'status'          => 'processing',
        'amount'          => $payload['amount'] * ($config['exchange'] ?? 23000),
        'user_id'         => $user->id,
        'username'        => $user->username,
        'trans_id'        => $data['trans_id'],
        'request_id'      => $requestId,
        'currency'        => 'USD',
        'description'     => 'Tạo hoá đơn OK',
        'payment_details' => [
          'amount'      => $data['amount'],
          'trans_id'    => $data['trans_id'],
          'request_id'  => $data['request_id'],
          'url_payment' => $data['url_payment'],
        ],
        'paid_at'         => null,
        'expired_at'      => now()->addHours(6),
      ]);

      return response()->json([
        'data'    => [
          'code'        => $invoice->code,
          'payment_url' => $data['url_payment'],
        ],
        'status'  => 200,
        'message' => 'Tạo hoá đơn thành công, bạn sẽ được chuyển đến trang thanh toán!',
      ], 200);
    }
  }
}
