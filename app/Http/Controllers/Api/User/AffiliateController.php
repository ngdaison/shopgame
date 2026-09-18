<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletLog;
use Helper;
use Illuminate\Http\Request;

use App\Models\GBOrder;

class AffiliateController extends Controller
{
  public function withdraw(Request $request)
  {
    $message     = [
      'amount.required'      => 'Vui lòng nhập số tiền muốn rút.',
      'amount.integer'       => 'Số tiền muốn rút phải là số.',
      'withdraw_to.required' => 'Vui lòng chọn kênh rút tiền.',
      'withdraw_to.string'   => 'Kênh rút tiền không hợp lệ.',
      'withdraw_to.in'       => 'Kênh rút tiền không hợp lệ.',
    ];
    $payload     = $request->validate([
      'amount'         => 'required|integer',
      'withdraw_to'    => 'required|string|in:wallet,bank',
      'bank_name'      => 'required_if:withdraw_to,bank|nullable|string',
      'account_number' => 'required_if:withdraw_to,bank|nullable|string',
      'account_name'   => 'required_if:withdraw_to,bank|nullable|string',
      'user_note'      => 'nullable|string',
    ], $message);
    $withdraw_to = $payload['withdraw_to'];



    $user = User::findOrFail(auth()->user()->id);

    if ($user->balance_1 < $payload['amount']) {
      return response()->json([
        'status'  => 400,
        'message' => 'Số dư hoa hồng không đủ để thực hiện giao dịch này.',
      ], 400);
    }

    $config = Helper::getConfig('affiliate_config');

    $min_withdraw    = $config['min_withdraw'] ?? 0;
    $max_withdraw    = $config['max_withdraw'] ?? 0;
    $withdraw_status = $config['withdraw_status'] ?? 0;

    if (!$withdraw_status) {
      return response()->json([
        'status'  => 400,
        'message' => 'Chức năng rút hoa hồng đang tạm khóa, vui lòng thử lại sau.',
      ], 400);
    }

    if ($payload['amount'] < $min_withdraw) {
      return response()->json([
        'status'  => 400,
        'message' => 'Số tiền rút tối thiểu là ' . number_format($min_withdraw) . 'đ.',
      ], 400);
    }

    if ($payload['amount'] > $max_withdraw) {
      return response()->json([
        'status'  => 400,
        'message' => 'Số tiền rút tối đa là ' . number_format($max_withdraw) . 'đ.',
      ], 400);
    }

    if ($withdraw_to === 'wallet') {
      $user->decrement('balance_1', $payload['amount']);

      $transaction = \App\Models\CollaTransaction::create([
        'user_id'        => $user->id,
        'username'       => $user->username,
        'type'           => 'affiliate_withdraw',
        'amount'         => $payload['amount'],
        'status'         => 'Pending',
        'order_id'       => 'AFF-' . strtoupper(Helper::randomString(8)),
        'description'    => '[Affiliate] Rút tiền về ví tài khoản website',
        'balance_before' => $user->balance_1 + $payload['amount'],
        'balance_after'  => $user->balance_1,
        'payment_info'   => [
          'method' => 'wallet',
        ],
        'user_note'      => $payload['user_note'] ?? '',
      ]);

      WalletLog::create([
        'type'           => 'affiliate',
        'amount'         => $payload['amount'],
        'status'         => 'Pending',
        'sys_note'       => 'Rút tiền về ví tài khoản website',
        'user_id'        => $user->id,
        'username'       => $user->username,
        'user_note'      => $payload['user_note'] ?? '',
        'order_id'       => $transaction->order_id,
        'request_id'     => Helper::randomString(10),
        'ip_address'     => request()->ip(),
        'user_action'    => $user->username,
        'balance_after'  => $user->balance_1,
        'balance_before' => $user->balance_1 + $payload['amount'],
        'channel_charge' => 'wallet',
      ]);

      return response()->json([
        'status'  => 200,
        'message' => 'Yêu cầu rút tiền về ví đã được gửi. Vui lòng chờ admin phê duyệt.',
      ], 200);
    } elseif ($withdraw_to === 'bank') {
      // Manual withdrawal for Bank
      $user->decrement('balance_1', $payload['amount']);

      $transaction = \App\Models\CollaTransaction::create([
        'user_id'        => $user->id,
        'username'       => $user->username,
        'type'           => 'affiliate_withdraw',
        'amount'         => $payload['amount'],
        'status'         => 'Pending',
        'order_id'       => 'AFF-' . strtoupper(Helper::randomString(8)),
        'description'    => '[Affiliate] Yêu cầu rút tiền về ngân hàng',
        'balance_before' => $user->balance_1 + $payload['amount'],
        'balance_after'  => $user->balance_1,
        'payment_info'   => [
          'bank_name'      => $payload['bank_name'],
          'account_number' => $payload['account_number'],
          'account_name'   => $payload['account_name'],
          'method'         => 'bank',
        ],
        'user_note'      => $payload['user_note'] ?? '',
      ]);

      WalletLog::create([
        'type'           => 'affiliate',
        'amount'         => $payload['amount'],
        'status'         => 'Pending',
        'sys_note'       => 'Rút tiền về ngân hàng',
        'user_id'        => $user->id,
        'username'       => $user->username,
        'user_note'      => $payload['user_note'] ?? '',
        'order_id'       => $transaction->order_id,
        'request_id'     => Helper::randomString(10),
        'ip_address'     => request()->ip(),
        'user_action'    => $user->username,
        'balance_after'  => $user->balance_1,
        'balance_before' => $user->balance_1 + $payload['amount'],
        'channel_charge' => 'bank',
      ]);

      return response()->json([
        'status'  => 200,
        'message' => 'Yêu cầu rút tiền đã được gửi. Vui lòng chờ admin phê duyệt.',
      ], 200);
    }
 else {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy kênh rút tiền mà bạn đã chọn, hãy xem lại.',
      ], 400);
    }
  }

  public function updateCode(Request $request)
  {
    $request->validate([
      'code' => 'nullable|string|alpha_num|max:50',
    ], [
      'code.alpha_num' => 'Mã giới thiệu chỉ được chứa chữ cái và số.',
      'code.max'       => 'Mã giới thiệu tối đa 50 ký tự.',
    ]);

    $user = auth()->user();
    $affiliate = \App\Models\Affiliate::where('user_id', $user->id)->first();

    if (!$affiliate) {
      return response()->json([
        'status'  => 404,
        'message' => 'Không tìm thấy thông tin tiếp thị liên kết.',
      ], 404);
    }

    $newCode = $request->code;

    // Generate random code if empty
    if (empty($newCode)) {
      do {
        $newCode = Helper::randomString(8);
      } while (\App\Models\Affiliate::where('code', $newCode)->exists());
    } else {
      // Check uniqueness if custom code is provided and different from current
      if ($newCode !== $affiliate->code) {
        $exists = \App\Models\Affiliate::where('code', $newCode)->exists();
        if ($exists) {
          return response()->json([
            'status'  => 400,
            'message' => 'Mã giới thiệu này đã được sử dụng bởi người khác.',
          ], 400);
        }
      }
    }

    $affiliate->update(['code' => $newCode]);

    // Update User model to sync referral_code
    $user->update(['referral_code' => $newCode]);

    return response()->json([
      'status'  => 200,
      'message' => 'Cập nhật mã giới thiệu thành công.',
      'data'    => [
        'code' => $newCode,
        'url'  => route('ref', ['ref' => $newCode])
      ]
    ]);
  }

  public function history(Request $request)
  {
    $limit = $request->input('limit', 10);
    $search = $request->input('search');

    $query = WalletLog::where('user_id', auth()->id())
      ->whereIn('type', ['commission', 'affiliate']) // Filter by commission/affiliate related logs
      ->orderBy('id', 'desc');

    if ($search) {
      $query->where('id', 'like', "%{$search}%");
    }

    $data = $query->paginate($limit);

    // Fetch order statuses if necessary
    $config = Helper::getConfig('affiliate_config');
    $commissionType = $config['commission_type'] ?? 'deposit'; // 'deposit' or 'product'

    if ($commissionType === 'order') {
        $data->getCollection()->transform(function ($log) {
             // Default status from log
             $log->display_status_code = 'warning'; 
             $log->display_status_text = 'Chờ Xử Lý'; // Should default to Pending if we are waiting for an order match or if order is missing?
             // Actually, if order_id is missing (old data), it means money was already given (Completed).
             // But if order_id is present, we should trust the order status.

             if ($log->type === 'commission') {
                  // Always show Completed as per new requirement
                  $log->display_status_code = 'success';
                  $log->display_status_text = 'Hoàn Thành';
                  
                  // Keep the logic for 'Cancelled' if needed, or just force Completed?
                  // User requested: "luôn luôn là Hoàn Thành" (always Completed).
                  // But if status in DB is Cancelled (e.g. refund), maybe we should still show Cancelled?
                  // The user said "khi giới thiệu được 1 người là sẽ được cộng tiền luôn... luôn luôn là Hoàn Thành".
                  // This implies positive flow. If it's cancelled, it might be different. 
                  // But let's stick to the core request: make it appear Completed.
                  
                  if ($log->status === 'Cancelled') {
                       $log->display_status_code = 'danger';
                       $log->display_status_text = 'Đã bị Hủy';
                  }
             }
            return $log;
        });
    }

    return response()->json([
      'status' => 200,
      'data'   => $data,
      'config' => ['commission_type' => $commissionType]
    ]);
  }

  public function withdrawHistory(Request $request)
  {
    $limit = $request->input('limit', 10);
    $search = $request->input('search');

    $query = \App\Models\CollaTransaction::where('user_id', auth()->id())
      ->where('type', 'affiliate_withdraw')
      ->orderBy('id', 'desc');

    if ($search) {
      $query->where('order_id', 'like', "%{$search}%");
    }

    $data = $query->paginate($limit);

    return response()->json([
      'status' => 200,
      'data'   => $data
    ]);
  }
}
