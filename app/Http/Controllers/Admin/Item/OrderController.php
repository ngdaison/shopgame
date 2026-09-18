<?php

namespace App\Http\Controllers\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\ItemOrder;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  public function index()
  {
    $query = ItemOrder::with('user:id,username,domain');

    // Filter by Admin Hide
    $query->whereNull('admin_deleted_at');

    // Filter by Role Limit Date
    $limitDate = auth()->user()->getHistoryLimitDate();
    if ($limitDate) {
        $query->where('created_at', '>=', $limitDate);
    }

    $orders = $query->get();

    // Load seller (account owner) from username for domain fallback
    $sellerNames = $orders->pluck('username')->unique()->filter();
    $sellers = collect();
    if ($sellerNames->count() > 0) {
      $sellers = User::whereIn('username', $sellerNames)
        ->select('id', 'username', 'domain')
        ->get()
        ->keyBy('username');
    }

    $orders->each(function ($item) use ($sellers) {
      // Gán seller cho view nếu cần
      if (isset($sellers[$item->username])) {
        $item->seller = $sellers[$item->username];
      }
    });

    return view('admin.items.orders.index', compact('orders'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:item_orders,id',
      'status' => 'required|in:Pending,Processing,Completed,Cancelled,Destroyed',
      'admin_note' => 'nullable|string|max:255',
      'order_note' => 'nullable|string|max:255',
    ]);

    $order = ItemOrder::findOrFail($payload['id']);

    if (in_array($order->status, ['Cancelled', 'Destroyed'])) {
      return redirect()->back()->with('error', 'Đơn hàng đã hoàn thành hoặc đã hủy');
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
          'type' => 'item-refund',
          'extras' => [],
          'status' => 'paid',
          'content' => 'Hoàn tiền đơn vật phẩm ' . $order->name,
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
  public function refund(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:item_orders,id',
    ]);

    $order = ItemOrder::findOrFail($payload['id']);

    if (in_array($order->status, ['Cancelled', 'Destroyed'])) {
      return response()->json([
        'status' => false,
        'message' => 'Đơn hàng đã hoàn thành hoặc đã hủy',
      ]);
    }

    try {
      \DB::beginTransaction();

      $order->update(['status' => 'Cancelled']);

      $client = User::find($order->user_id);

      if ($client) {
        $client->increment('balance', $order->payment);

        $client->transactions()->create([
          'code' => $order->code,
          'amount' => $order->payment,
          'balance_after' => $client->balance,
          'balance_before' => $client->balance - $order->payment,
          'type' => 'item-refund',
          'extras' => [],
          'status' => 'paid',
          'content' => 'Hoàn tiền đơn vật phẩm ' . $order->name,
          'user_id' => $client->id,
          'username' => $client->username,
        ]);
      }

      Helper::addHistory('Hoàn tiền đơn hàng vật phẩm ' . $order->id);

      \DB::commit();

      return response()->json([
        'status' => true,
        'message' => 'Hoàn tiền đơn hàng thành công',
      ]);
    }
    catch (\Exception $e) {
      \DB::rollBack();
      return response()->json([
        'status' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
      ]);
    }
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
          'id' => 'required|exists:item_orders,id',
      ]);

      ItemOrder::where('id', $request->id)->update([
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

      // Hide all item orders except those belonging to Product Managers
      ItemOrder::whereNull('admin_deleted_at')
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