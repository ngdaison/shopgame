<?php

namespace App\Http\Controllers\Staff\Order;

use App\Http\Controllers\Controller;
use App\Models\ItemOrder;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class ItemController extends Controller
{
  public function index(Request $request)
  {
    $user = User::find(auth()->user()->id);

    // back to home if user is not a items
    if (!is_array($user->colla_type) || !in_array('items', $user->colla_type)) {
      return redirect()->route('staff.dashboard');
    }

    $pendingOrders    = ItemOrder::where('status', 'Pending')
      ->orderBy('created_at', 'desc')
      ->paginate(10, ['*'], 'pending');
    $claimedOrders    = ItemOrder::where('assigned_to', $user->username)
      ->orderBy('created_at', 'desc')
      ->paginate(20, ['*'], 'claimed');

    return view('staff.orders.items', compact('pendingOrders', 'claimedOrders', 'user'));
  }

  public function claim(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|integer',
    ]);
    $user    = User::find(auth()->user()->id);

    if (!is_array($user->colla_type) || !in_array('items', $user->colla_type)) {
      return response()->json([
        'data'    => 400,
        'message' => 'Bạn không có quyền nhận đơn hàng',
      ], 400);
    }

    $order = ItemOrder::where('id', $payload['id'])
      ->where('assigned_to', null)
      ->where('status', 'Pending')
      ->firstOrFail();

    $lastClaimed = ItemOrder::where('assigned_to', auth()->user()->username)
      ->where('status', 'Assigned')
      ->orderBy('assigned_at', 'desc')
      ->first();

    if ($lastClaimed && $seconds = $lastClaimed->assigned_at->diffInSeconds(now()) < 10) {
      return response()->json([
        'data'    => 400,
        'message' => 'Vui lòng chờ ' . $seconds . ' giây trước khi nhận thêm đơn mới'
      ], 400);
    }

    if ($order->assigned_to !== null) {
      return response()->json([
        'data'    => 400,
        'message' => 'Ui ui, đơn hàng này đã được nhận rồi',
      ], 400);
    }

    $order->update([
      'status'          => 'Assigned',
      'assigned_to'     => auth()->user()->username,
      'assigned_at'     => now(),
      'assigned_type'   => 'staff',
      'assigned_status' => 'Processing',
    ]);

    Helper::addHistory("Đã nhận đơn hàng vật phẩm #{$order->code}");

    return response()->json([
      'data'    => 200,
      'message' => 'Nhận đơn hàng thành công #' . $order->code
    ], 200);
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'         => 'required|exists:item_orders,id',
      'status'     => 'required|in:Assigned,Processing,Completed,Cancelled',
      'admin_note' => 'nullable|string|max:255',
      'order_note' => 'nullable|string|max:255',
    ]);

    $user = User::find(auth()->user()->id);

    if (!is_array($user->colla_type) || !in_array('items', $user->colla_type)) {
      return response()->json(['status' => false, 'message' => 'Bạn không có quyền cập nhật đơn hàng']);
    }

    $order = ItemOrder::where('id', $payload['id'])->where('assigned_to', $user->username)->firstOrFail();

    if (in_array($order->status, ['Completed', 'Cancelled'])) {
      return response()->json(['status' => false, 'message' => 'Đơn hàng đã hoàn thành hoặc đã hủy']);
    }

    $order->update($payload);

    if ($payload['status'] === 'Cancelled') {
      $client = User::find($order->user_id);

      if ($client) {
        $client->increment('balance', $order->payment);

        $client->transactions()->create([
          'code'           => $order->code,
          'amount'         => $order->payment,
          'balance_after'  => $client->balance,
          'balance_before' => $client->balance - $order->payment,
          'type'           => 'item-refund',
          'extras'         => [],
          'status'         => 'paid',
          'content'        => 'Hoàn tiền đơn vật phẩm ' . $order->name,
          'user_id'        => $client->id,
          'username'       => $client->username,
        ]);

        $order->update([
          'payment' => 0,
        ]);
      }
    } else if ($payload['status'] === 'Completed') {
      $payment = (float) (($order->payment * $user->colla_percent) / 100);

      // Calculate warranty expiration from now (completion time)
      $warrantyHours = 0;
      // Try to get from ItemData if available in extra_data or lookup
      $itemData = \App\Models\ItemData::find($order->data['id'] ?? 0);
      if ($itemData) {
          if ($itemData->warranty_hours > 0) {
              $warrantyHours = $itemData->warranty_hours;
          } elseif ($itemData->group && $itemData->group->warranty_hours > 0) {
              $warrantyHours = $itemData->group->warranty_hours;
          }
      }

      $warrantyExpireAt = $warrantyHours > 0 ? now()->addHours($warrantyHours) : null;

      $order->update([
        'assigned_note'      => 'Đã hoàn thành đơn hàng',
        'assigned_status'    => 'WaitPayment',
        'assigned_payment'   => $payment,
        'assigned_complain'  => false,
        'assigned_completed' => now(),
        'warranty_expire_at' => $warrantyExpireAt,
      ]);
    }

    Helper::addHistory('Cập nhật đơn hàng ' . $order->id . ' trạng thái ' . $payload['status']);

    return response()->json(['status' => true, 'message' => 'Cập nhật đơn hàng thành công']);
  }
}
