<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
  public function cardList(Request $request)
  {
    $payload = $request->validate([
      'page'      => 'nullable|integer',
      'limit'     => 'nullable|integer',
      'search'    => 'nullable|string',
      'sort_by'   => 'nullable|string',
      'sort_type' => 'nullable|string|in:asc,desc',
    ]);

    $query = Card::where('user_id', auth()->user()->id);

    if (isset($payload['search'])) {
      $query = $query->where('serial', 'like', '%' . $payload['search'] . '%')
        ->orWhere('code', 'like', '%' . $payload['search'] . '%');
    }

    if (isset($payload['sort_by'])) {
      $query = $query->orderBy($payload['sort_by'], $payload['sort_type'] ?? 'asc');
    }

    $meta = [
      'page'       => (int) ($payload['page'] ?? 1),
      'limit'      => (int) ($payload['limit'] ?? 10),
      'total_rows' => $query->count(),
      'total_page' => ceil($query->count() / ($payload['limit'] ?? 10)),
    ];

    $data = $query->skip(($meta['page'] - 1) * $meta['limit'])->take($meta['limit']);

    return response()->json([
      'data'    => [
        'meta' => $meta,
        'data' => $data->get(),
      ],
      'status'  => 200,
      'message' => 'Lấy danh sách thẻ cào thành công',
    ], 200);

  }

  public function sendCard(Request $request)
  {

    $payload = $request->validate([
      'code'   => 'required|string',
      'telco'  => 'required|string|in:VIETTEL,VINAPHONE,MOBIFONE,ZING,GARENA,VNMOBI',
      'amount' => 'required|integer|in:10000,20000,30000,50000,100000,200000,300000,500000,1000000',
      'serial' => 'required|string',
    ]);

    if (Cache::has('locked_' . $request->ip())) {
      return response()->json([
        'status'  => 400,
        'message' => 'Phát hiện nghi vấn spam, bạn tạm thời bị khóa',
      ], 400);
    }

    $user = User::find(auth()->user()->id);

    if ($user === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Tài khoản không tồn tại',
      ], 400);
    }

    $code   = $payload['code'];
    $telco  = $payload['telco'];
    $amount = $payload['amount'];
    $serial = $payload['serial'];
    $config = Helper::getApiConfig('charging_card');

    $fees  = $config['fees'][$telco] ?? 20;
    
    // Check specific fees
    if (isset($config['specific_fees'][$telco][$amount])) {
        $fees = $config['specific_fees'][$telco][$amount];
    }
    $count = Card::where('user_id', $user->id)->where('status', 'Processing')->count();

    if ($count >= 3 && $user->role !== 'admin') {
      return response()->json([
        'status'  => 400,
        'message' => 'Bạn chỉ được gửi 3 thẻ cùng lúc cho đến khi được duyệt',
      ], 400);
    }

    // if failed > 6 times in 5 minutes => block 6 hours
    $failedCount = Card::where('user_id', $user->id)->where('status', 'Error')->where('created_at', '>=', now()->subMinutes(5))->count();
    if ($failedCount >= 3) {
      // $user->update([
      //   'status' => 'blocked',
      // ]);

      Cache::put('locked_' . $request->ip(), true, 43200);

      return response()->json([
        'status'  => 400,
        'message' => 'Phát hiện nghi vấn spam, tài khoản của bạn đã bị khóa',
      ], 400);
    }

    if (!isset($config['api_url']) || !isset($config['partner_id']) || !isset($config['partner_key'])) {
      return response()->json([
        'status'  => 400,
        'message' => 'Chức năng này đang được bảo trì, vui lòng quay lại sau',
      ], 400);
    }

    $requestId = $user->username . '_' . str()->random(6);

    // Use mapped API code if available
    $apiTelco = $config['mapping'][$telco] ?? $telco;

    $formData = [
      'telco'      => $apiTelco,
      'code'       => $code,
      'serial'     => $serial,
      'amount'     => $amount,
      'request_id' => $requestId,
      'partner_id' => $config['partner_id'],
      'sign'       => md5($config['partner_key'] . $code . $serial),
      'command'    => 'charging',
    ];

    try {
      // Log Request
      Log::info("User {$user->id} sending card: Telco={$apiTelco}, Amount={$amount}, Code={$code}, Serial={$serial}, URL={$config['api_url']}");

      $resonpose = Http::withOptions(['verify' => false])->timeout(180)->post($config['api_url'] . '/chargingws/v2', $formData);
      
      // Log Response
      Log::info("Card Response for {$requestId}: Status=" . $resonpose->status() . " Body=" . $resonpose->body());

      if ($resonpose->failed()) {
        return response()->json([
          'data'    => $resonpose->body(),
          'status'  => 400,
          'message' => $resonpose->json('message', 'Lỗi kết nối API (HTTP ' . $resonpose->status() . ')'),
        ], 400);
      }
      
      $result = $resonpose->json();

      if ($result === null) {
          Log::error("Card Response JSON Decode Failed: " . $resonpose->body());
          return response()->json([
            'status'  => 400,
            'message' => 'Lỗi phản hồi từ nhà cung cấp (Invalid JSON).',
            'debug'   => $user->role === 'admin' ? substr($resonpose->body(), 0, 100) : null
          ], 400);
      }

      if (isset($result['trans_id'])) {
        Card::create([
          'type'           => $telco,
          'code'           => $code,
          'serial'         => $serial,
          'value'          => $result['declared_value'] ?? $amount,
          'amount'         => $amount - ($amount * $fees) / 100,
          'status'         => 'Processing',
          'user_id'        => $user->id,
          'username'       => $user->username,
          'request_id'     => $requestId,
          'content'        => $result['message'] ?? 'Unknow error',
          'order_id'       => $result['trans_id'] ?? -1,
          'channel_charge' => $config['api_url'],
        ]);

        return response()->json([
          'status'  => 200,
          'message' => 'Thẻ của bạn đang được xử lý, vui lòng chờ',
        ], 200);
      } else {
        return response()->json([
          'data'    => $user->role === 'admin' ? $result : null,
          'status'  => 400,
          'message' => $this->parseCardError($result['message'] ?? 'Error while processing your request'),
        ], 400);
      }
    } catch (\Exception $e) {
      Log::error("Send Card Exception: " . $e->getMessage() . " Trace: " . $e->getTraceAsString());
      return response()->json([
        'data'    => $user->role === 'admin' ? $e->getMessage() : null,
        'status'  => 400,
        'message' => 'Lỗi kết nối API: ' . $e->getMessage(),
      ], 400);
    }
  }

  private function parseCardError($string)
  {
    $string = strtolower($string);

    switch (($string)) {
      case 'charging.card_existed':
        return 'Thẻ này đã tồn tại trên hệ thống';
      case 'invalid_card':
        return 'Thẻ không hợp lệ hoặc đã được sử dụng';
      case 'charging.invalid_card_code':
        return 'Mã thẻ không hợp lệ';
      case 'charging.invalid_card_serial':
        return 'Số serial không hợp lệ';
      case 'lang.invalid_serial_code':
        return 'Số serial hoặc mã thẻ không hợp lệ';
      case 'lang.invalid_card_code':
        return 'Mã thẻ không hợp lệ';
      default:
        return ucfirst($string);
    }
  }
}
