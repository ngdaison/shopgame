<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\GBGroup;
use App\Models\GBOrder;
use App\Models\GBPackage;
use App\Models\GBProduct;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class BoostingGameController extends Controller
{
  // ... existing methods ...

  public function buyMultiple(Request $request)
  {
    $payload = $request->validate([
      'product_codes' => 'required|array|min:1',
      'product_codes.*' => 'required|string',
      'order_note'  => 'nullable|string',
      'input_user'  => 'required|string',
      'input_pass'  => 'required|string',
      'input_extra' => 'nullable|string',
    ]);

    $user = User::find($request->user()?->id);

    if ($user === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không xác thực được thông tin người dùng',
      ], 400);
    }

    if ($user->status !== 'active') {
      return response()->json([
        'status'  => 400,
        'message' => 'Tài khoản của bạn đã bị vô hiệu hoá',
      ], 400);
    }

    // Get all products
    $products = GBProduct::with('package')->whereIn('code', $payload['product_codes'])->get();

    if ($products->count() === 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy sản phẩm nào',
      ], 400);
    }

    // Calculate total price
    $totalPrice = $products->sum('price');

    if ($totalPrice <= 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không thể tính tiền, vui lòng thử lại',
      ], 400);
    }

    if ($user->balance < $totalPrice) {
      $require = $totalPrice - $user->balance;
      return response()->json([
        'status'  => 400,
        'message' => 'Bạn còn thiếu ' . Helper::formatCurrency($require) . ' để mua!',
      ], 400);
    }

    // Deduct balance
    if (!$user->decrement('balance', $totalPrice)) {
      return response()->json([
        'status'  => 400,
        'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
      ], 400);
    }

    // Create single order with all products
    $code = 'GB-' . Helper::randomString(8, true);
    $productNames = $products->pluck('name')->join(', ');
    
    $firstProduct = $products->first();
    
    $order = GBOrder::create([
      'name'        => $productNames,
      'code'        => $code,
      'input_user'  => $payload['input_user'],
      'input_pass'  => $payload['input_pass'],
      'input_extra' => $payload['input_extra'] ?? '',
      'payment'     => $totalPrice,
      'status'      => 'Pending',
      'user_id'     => $user->id,
      'username'    => $user->username,
      'package_id'  => $firstProduct->package_id,
      'group_id'    => $firstProduct->package->group_id,
      'order_note'  => $payload['order_note'] ?? '',
      'extras'      => [
        'products' => $products->map(function($p) {
          return [
            'code' => $p->code,
            'name' => $p->name,
            'price' => $p->price,
          ];
        })->toArray()
      ]
    ]);

    // Update sold count for all packages
    foreach ($products as $product) {
      $product->package->update([
        'sold_count' => $product->package->sold_count + 1,
      ]);
    }

    $group = isset($firstProduct->package->group) ? $firstProduct->package->group->name : '-';

    // Create transaction
    $user->transactions()->create([
      'code'           => $code,
      'amount'         => $totalPrice,
      'balance_after'  => $user->balance,
      'balance_before' => $user->balance + $totalPrice,
      'type'           => 'boosting-buy',
      'extras'         => [
        'group_id'   => $firstProduct->package->group_id,
        'package_id' => $firstProduct->package_id,
        'products'   => $products->pluck('code')->toArray(),
      ],
      'status'         => 'paid',
      'content'        => 'Thuê ' . $products->count() . ' gói cày; Nhóm ' . $group,
      'user_id'        => $user->id,
      'username'       => $user->username,
    ]);

    try {
      $ref = $user->referrer;
      if ($ref !== null) {
        $affiliate = $ref->affiliate;
        if ($affiliate !== null) {
          $affiliate->increment('total_boost_buy', $products->count());
        }
      }

      Helper::sendMessageTelegram("🎮🎮🎮 ĐƠN HÀNG CÀY THUÊ 🎮🎮🎮\nMã đơn: " . $order->code . "\nSố gói: " . $products->count() . "\nDịch vụ: " . $productNames . "\nThanh toán: " . Helper::formatCurrency($order->payment) . "\nTài khoản: " . $user->username . "\nGhi chú: " . $order->order_note . "\nThời gian: " . $order->created_at . "\n");

      Helper::sendMail([
        'cc'      => setting('email'),
        'to'      => $user->email,
        'subject' => 'Đơn hàng cày thuê ' . $order->code . ' của bạn đã được tạo',
        'content' => "Xin chào, <strong>{$user->username}</strong><br><br>Số gói: <strong>{$products->count()}</strong><br />Dịch vụ: <strong>{$productNames}</strong><br /><br />Đơn hàng: <strong>{$order->code}</strong> của bạn đã được tạo thành công.<br><br>Chúng tôi sẽ xử lý đơn hàng của bạn trong thời gian sớm nhất.<br><br>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.<br><br>Trân trọng,<br><strong>Team " . config('app.name') . "</strong>",
      ]);
    } catch (\Exception $e) {
      // loi
    }

    return response()->json([
      'data'    => [
        'code'    => $code,
        'name'    => $productNames,
        'payment' => $totalPrice,
        'count'   => $products->count(),
      ],
      'status'  => 200,
      'message' => 'Tạo đơn hàng thành công với ' . $products->count() . ' gói dịch vụ',
    ], 200);
  }
}
