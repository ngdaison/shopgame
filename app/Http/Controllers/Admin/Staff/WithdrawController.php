<?php

namespace App\Http\Controllers\Admin\Staff;

use App\Http\Controllers\Controller;
use App\Models\CollaTransaction;
use Helper;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
  public function index(Request $request)
  {
    $query = CollaTransaction::whereIn('type', ['staff_withdraw', 'affiliate_withdraw']);

    $limitDate = auth()->user()->getHistoryLimitDate();
    if ($limitDate) {
      $query->where('created_at', '>=', $limitDate);
    }

    $withdraws = $query->orderBy('id', 'desc')
      ->get();

    return view('admin.staff.withdraws.index', compact('withdraws'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'        => 'required|integer|exists:colla_transactions,id',
      'status'    => 'required|string|in:Pending,Completed,Cancelled,Declined',
      'user_note' => 'nullable|string',
    ]);

    $withdrawal = CollaTransaction::findOrFail($payload['id']);

    if ($withdrawal->status === $payload['status']) {
      return response()->json([
        'status'  => false,
        'message' => 'Trạng thái yêu cầu không thay đổi.',
      ]);
    }

    if ($withdrawal->status !== 'Pending') {
      return response()->json([
        'status'  => false,
        'message' => 'Chỉ có thể cập nhật yêu cầu đang chờ xử lý.',
      ]);
    }

    $withdrawal->update([
      'status'    => $payload['status'],
      'user_note' => $payload['user_note'] ?? $withdrawal->user_note,
    ]);

    // Sync status to WalletLog
    $walletLog = \App\Models\WalletLog::where('order_id', $withdrawal->order_id)->first();
    if ($walletLog) {
      $walletLog->update(['status' => $payload['status']]);
    }

    $user = \App\Models\User::find($withdrawal->user_id);

    if ($user) {
      if ($payload['status'] === 'Completed') {
        // If withdrawal is to Wallet (Main Account), add balance to Main Account
        if (isset($withdrawal->payment_info['method']) && $withdrawal->payment_info['method'] === 'wallet') {
          $user->increment('balance', $withdrawal->amount);

          // Log Transaction for Main Account
          \App\Models\Transaction::create([
            'user_id'        => $user->id,
            'username'       => $user->username,
            'amount'         => $withdrawal->amount,
            'type'           => 'withdraw_affiliate',
            'status'         => 'completed',
            'code'           => $withdrawal->order_id,
            'order_id'       => $withdrawal->order_id,
            'balance_before' => $user->balance - $withdrawal->amount,
            'balance_after'  => $user->balance,
            'content'        => 'Rút tiền từ hoa hồng về ví chính',
            'domain'         => Helper::getDomain(),
          ]);
        }
      } elseif ($payload['status'] === 'Cancelled') {
        // Refund Affiliate Balance
        $user->increment('balance_1', $withdrawal->amount);
        
        // No need to create new WalletLog, just updating status of the original one is enough 
        // as the user sees the money returned to balance_1. 
        // Ideally we could add a refund log, but standard practice often just reverts.
      } elseif ($payload['status'] === 'Declined') {
        // No Refund
      }
    }

    Helper::addHistory("Cập nhật trạng thái yêu cầu rút tiền CTV #{$withdrawal->id} thành {$payload['status']}");

    session()->flash('success', 'Cập nhật trạng thái yêu cầu rút tiền thành công.');

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật trạng thái yêu cầu rút tiền thành công.',
    ]);
  }
}
