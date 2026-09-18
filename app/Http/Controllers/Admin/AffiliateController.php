<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletLog;
use Illuminate\Http\Request;

class AffiliateController extends Controller
{
  public function index()
  {
    $referrals = User::whereNotNull('referral_by')->get();
    
    // Get affiliate withdrawal logs for users who have a referrer
    $histories = WalletLog::where('type', 'affiliate')
      ->whereIn('user_id', $referrals->pluck('id'))
      ->get();

    $totalAmount          = $histories->sum('amount');
    $totalAmountPending   = $histories->where('status', 'Pending')->sum('amount');
    $totalAmountCompleted = $histories->where('status', 'Completed')->sum('amount');
    $totalAmountCancelled = $histories->where('status', 'Cancelled')->sum('amount');

    return view('admin.affiliate.index', compact('referrals', 'histories', 'totalAmount', 'totalAmountPending', 'totalAmountCompleted', 'totalAmountCancelled'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'        => 'required|integer|exists:wallet_logs',
      'status'    => 'required|string|in:Completed,Cancelled,Pending',
      'user_note' => 'nullable|string|max:255',
      // 'sys_note'  => 'nullable|string|max:255',
    ]);

    $history = WalletLog::findOrFail($payload['id']);

    if ($history->status !== 'Pending') {
      return response()->json([
        'status'  => false,
        'message' => 'Không thể cập nhật lịch sử này nữa.',
      ]);
    }

    $history->update([
      'status'    => $payload['status'],
      'user_note' => $payload['user_note'],
      // 'sys_note'  => $payload['sys_note'],
    ]);

    if ($payload['status'] === 'Cancelled') {
      $user = User::find($history->user_id);
      if ($user !== NULL) {
        $user->increment('balance_1', $history->amount);
        WalletLog::create([
          'type'           => 'commission',
          'amount'         => $history->amount,
          'status'         => 'Completed',
          'user_id'        => $user->id,
          'username'       => $user->username,
          'sys_note'       => $user->username,

          'user_note'      => 'Rút hoa hồng #' . $history->id . ' bị hủy',
          'user_action'    => 'increment',
          'ip_address'     => '127.0.0.1',
          'balance_after'  => $user->balance_1,
          'balance_before' => $user->balance_1 - $history->amount
        ]);
      }
    }

    session()->flash('success', 'Cập nhật lịch sử thành công.');

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật lịch sử thành công.',
    ]);
  }
}
