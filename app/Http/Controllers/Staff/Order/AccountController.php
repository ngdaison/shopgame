<?php

namespace App\Http\Controllers\Staff\Order;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\ListItem;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class AccountController extends Controller
{
  public function index(Request $request, $id = null)
  {
    $user = auth()->user();
    if (!is_array($user->colla_type) || !in_array('account', $user->colla_type)) {
        return redirect()->route('staff.dashboard');
    }

    if ($id === null) {
      $payload = $request->validate([
        'sold'       => 'nullable|in:0,1',
        'group'      => 'nullable|integer',
        'username'   => 'nullable|string',
        'buyer_name' => 'nullable|string',
        'start_date' => 'nullable|date',
        'end_date'   => 'nullable|date',
        'domain'     => 'nullable|string',
      ]);

      $groups = Group::orderBy('id', 'desc')->whereIn('id', auth()->user()->staff_group_ids)->get();
      $items  = ListItem::query();

      if (isset($payload['sold']) && $payload['sold'] === '1') {
        $items = $items->where('buyer_name', '!=', null);
      } elseif (isset($payload['sold']) && $payload['sold'] === '0') {
        $items = $items->where('buyer_name', null);
      }

      if (isset($payload['group']) && $payload['group'] !== null) {
        $items = $items->where('group_id', $payload['group']);
      }

      if (isset($payload['username']) && $payload['username'] !== null) {
        $items = $items->where('username', 'like', '%' . $payload['username'] . '%');
      }

      if (isset($payload['buyer_name']) && $payload['buyer_name'] !== null) {
        $items = $items->where('buyer_name', 'like', '%' . $payload['buyer_name'] . '%');
      }

      if (isset($payload['start_date']) && $payload['start_date'] !== null) {
        $items = $items->whereDate('created_at', '>=', $payload['start_date']);
      }

      if (isset($payload['end_date']) && $payload['end_date'] !== null) {
        $items = $items->whereDate('created_at', '<=', $payload['end_date']);
      }

      if (isset($payload['domain']) && $payload['domain'] !== null) {
        $items = $items->where('domain', 'like', '%' . $payload['domain'] . '%');
      }

      $items = $items->orderBy('id', 'desc')->where('staff_name', auth()->user()->username)->get();

      return view('staff.orders.accounts', compact('items', 'groups', 'payload'));
    } else {
      // $group = Group::findOrFail($id);

      // return view('staff.orders.accounts', compact('group'));
    }
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'         => 'required|exists:list_items,id',
      'status'     => 'required|in:Assigned,Processing,Completed,Cancelled',
      'order_note' => 'nullable|string|max:255',
    ]);

    $user = User::find(auth()->user()->id);

    // Verify staff permission/ownership
    // Note: accounts implicitly uses 'staff_name' to track assignment
    $order = ListItem::where('id', $payload['id'])->where('staff_name', $user->username)->firstOrFail();

    if (!is_array($user->colla_type) || !in_array('boosting', $user->colla_type)) {
      return response()->json([
        'data'    => 400,
        'message' => 'Bạn không có quyền nhận đơn hàng',
      ], 400);
    }

    if (in_array($order->staff_status, ['Completed', 'Cancelled'])) {
      return response()->json(['status' => false, 'message' => 'Đơn hàng đã hoàn thành hoặc đã hủy']);
    }

    $order->update([
        'staff_status' => $payload['status'],
        'order_note'   => $payload['order_note'] ?? $order->order_note
    ]);

    if ($payload['status'] === 'Cancelled') {
      if ($order->buyer_name) {
          $client = User::where('username', $order->buyer_name)->first();

          if ($client) {
            $client->increment('balance', $order->payment);

            $client->transactions()->create([
              'code'           => $order->buyer_code ?? 'REFUND-' . $order->id,
              'amount'         => $order->payment,
              'balance_after'  => $client->balance,
              'balance_before' => $client->balance - $order->payment,
              'type'           => 'account-refund',
              'extras'         => [],
              'status'         => 'paid',
              'content'        => 'Hoàn tiền tài khoản #' . $order->id . ' | ' . $order->name,
              'user_id'        => $client->id,
              'username'       => $client->username,
            ]);

            // Reset sold status
            $order->update([
              'buyer_name' => null,
              'buyer_code' => null,
              'buyer_date' => null,
              'buyer_paym' => null,
              'staff_payment' => 0,
            ]);
          }
      }
    } else if ($payload['status'] === 'Completed') {
      // Handle Affiliate Commission Completion
      // Assuming buyer_code is the order_id for commission
      if ($order->buyer_code) {
          $walletLog = \App\Models\WalletLog::where('order_id', $order->buyer_code)
              ->where('type', 'commission')
              ->first();
          
          if ($walletLog && $walletLog->status === 'Pending') {
              $affiliate = \App\Models\User::find($walletLog->user_id);
              if ($affiliate) {
                  $affiliate->increment('balance_1', $walletLog->amount);
                  $affiliate->refresh();
                  
                  $walletLog->update([
                      'status'         => 'Completed',
                      'balance_before' => $affiliate->balance_1 - $walletLog->amount,
                      'balance_after'  => $affiliate->balance_1
                  ]);
              }
          }
      }

      $payment = (float) (($order->payment * $user->colla_percent) / 100);

      // Calculate warranty expiration
      $warrantyHours = 0;
      if ($order->warranty_hours > 0) {
          $warrantyHours = $order->warranty_hours;
      } elseif ($order->group && $order->group->warranty_hours > 0) {
          $warrantyHours = $order->group->warranty_hours;
      }
      
      $warrantyExpireAt = $warrantyHours > 0 ? now()->addHours($warrantyHours) : null;

      $order->update([
        'staff_status'       => 'WaitPayment', // Set to WaitPayment for approval
        'staff_payment'      => $payment,
        'staff_completed_at' => now(),
        'warranty_expire_at' => $warrantyExpireAt,
      ]);
    }

    Helper::addHistory('Cập nhật tài khoản #' . $order->id . ' trạng thái ' . $payload['status']);

    return response()->json(['status' => true, 'message' => 'Cập nhật đơn hàng thành công']);
  }
}
