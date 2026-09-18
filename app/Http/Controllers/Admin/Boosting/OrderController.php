<?php

namespace App\Http\Controllers\Admin\Boosting;

use App\Http\Controllers\Controller;
use App\Models\GBOrder;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  public function index()
  {
    $query = GBOrder::with('user:id,username,domain');

    // Filter by Admin Hide
    $query->whereNull('admin_deleted_at');

    // Filter by Role Limit Date
    $limitDate = auth()->user()->getHistoryLimitDate();
    if ($limitDate) {
        $query->where('created_at', '>=', $limitDate);
    }

    $orders = $query->get();

    return view('admin.boosting.orders.index', compact('orders'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:g_b_orders,id',
      'status' => 'required|in:Pending,Processing,Completed,Cancelled,Destroyed',
      'admin_note' => 'nullable|string|max:255',
      'order_note' => 'nullable|string|max:2555',
    ]);

    $order = GBOrder::findOrFail($payload['id']);

    if (in_array($order->status, ['Cancelled', 'Destroyed'])) {
      return response()->json([
        'status' => false,
        'message' => 'Đơn hàng đã hoàn thành hoặc đã hủy',
      ]);
    }

    $order->update($payload);

    if ($payload['status'] === 'Cancelled') {
      $client = User::find($order->user_id);

      if ($client) {
        $client->increment('balance', $order->payment);

        $client->transactions()->create([
          'code' => $order->code,
          'amount' => $order->payment,
          'balance_after' => $client->balance,
          'balance_before' => $client->balance - $order->payment,
          'type' => 'boosting-refund',
          'extras' => [],
          'status' => 'paid',
          'content' => 'Hoàn tiền đơn cày thuê ' . $order->name,
          'user_id' => $client->id,
          'username' => $client->username,
        ]);
      }
    }

    Helper::addHistory('Cập nhật đơn hàng ' . $order->id . ' trạng thái ' . $payload['status']);


    return response()->json([
      'status' => true,
      'message' => 'Cập nhật đơn hàng thành công',
    ]);
  }

  public function delete(Request $request)
  {
    if (auth()->user()->hasRole('Product Manager')) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền ẩn đơn hàng!'
        ], 403);
    }

    $request->validate([
      'id' => 'required|exists:g_b_orders,id',
    ]);

    GBOrder::where('id', $request->id)->update([
      'admin_deleted_at' => now(),
    ]);

    return response()->json([
      'status' => true,
      'message' => 'Đơn hàng đã được ẩn thành công!',
    ]);
  }

  public function clearAll(Request $request)
  {
    if (auth()->user()->hasRole('Product Manager')) {
        return response()->json([
            'status' => false,
            'message' => 'Bạn không có quyền dọn dẹp đơn hàng!'
        ], 403);
    }

    // Hide all boosting orders except those belonging to Product Managers
    GBOrder::whereNull('admin_deleted_at')
        ->where(function ($query) {
            $query->whereDoesntHave('user')
                ->orWhereHas('user', function ($q) {
                    $q->where('role', 'not like', '%Product Manager%');
                });
        })
        ->update(['admin_deleted_at' => now()]);

    return response()->json([
      'status' => true,
      'message' => 'Toàn bộ đơn hàng đã được dọn dẹp thành công!',
    ]);
  }
}