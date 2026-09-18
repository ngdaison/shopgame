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
  public function index(Request $request)
  {
    $payload   = $request->validate([
      'page'      => 'nullable|integer',
      'limit'     => 'nullable|integer',
      'price'     => 'nullable|string',
      'search'    => 'nullable|string',
      'sort_by'   => 'nullable|string',
      'group_id'  => 'required|integer',
      'sort_type' => 'nullable|string|in:asc,desc',
    ]);
    $page      = $payload['page'] ?? 1;
    $limit     = $payload['limit'] ?? 10;
    $search    = $payload['search'] ?? null;
    $offset    = ($page - 1) * $limit;
    $sort_by   = $payload['sort_by'] ?? 'id';
    $sort_type = $payload['sort_type'] ?? 'asc';

    $group = GBGroup::where('id', $payload['group_id'])->where('status', true)->first();

    if ($group === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy nhóm dịch vụ này',
      ], 400);
    }

    $query = $group->packages()->where('status', true);

    if (isset ($search)) {
      $query = $query->where('name', 'like', '%' . $search . '%')
        ->orWhere('code', 'like', '%' . $search . '%');
    }

    if (isset ($payload['sort_by'])) {
      $query = $query->orderBy($payload['sort_by'], $payload['sort_type'] ?? 'asc');
    } else {
      $query = $query->orderBy('id', 'desc');
    }

    if (isset ($payload['price'])) {
      $price = explode('-', $payload['price']);
      if (count($price) === 2) {
        if (is_numeric($price[0]) && is_numeric($price[1])) {
          if ($price[1] <= 0) {
            $query = $query->where('price', '>=', $price[0]);
          } else {
            $query = $query->whereBetween('price', [$price[0], $price[1]]);
          }
        }
      }
    }

    $meta = [
      'page'       => (int) $page,
      'limit'      => (int) $limit,
      'total_rows' => $query->count(),
      'total_page' => ceil($query->count() / $limit),
    ];

    $data = $query->skip($offset)
      ->take($limit)
      ->orderBy($sort_by, $sort_type)
      ->get();

    return response()->json([
      'data'    => [
        'meta' => $meta,
        'data' => $data,
      ],
      'status'  => 200,
      'message' => 'Lấy danh sách dịch vụ thành công',
    ], 200);
  }

  public function products(Request $request)
  {
    $payload = $request->validate([
      'group_id'   => 'required|integer',
      'package_id' => 'nullable|integer',
      'search'     => 'nullable|string',
    ]);

    $group = GBGroup::where('id', $payload['group_id'])->where('status', true)->first();

    if ($group === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy nhóm dịch vụ này',
      ], 400);
    }

    $query = GBProduct::whereHas('package', function ($q) use ($group) {
      $q->where('group_id', $group->id)->where('status', true);
    })->where('status', true);

    if (isset ($payload['search']) && $payload['search'] !== '') {
      $search = $payload['search'];
      
      // Function to build accent-insensitive regex pattern
      $buildRegexPattern = function($str) {
          $replacements = [
              'a' => '[aáàảãạâấầẩẫậăắằẳẵặ]',
              'e' => '[eéèẻẽẹêếềểễệ]',
              'i' => '[iíìỉĩị]',
              'o' => '[oóòỏõọôốồổỗộơớờởỡợ]',
              'u' => '[uúùủũụưứừửữự]',
              'y' => '[yýỳỷỹỵ]',
              'd' => '[dđ]'
          ];
          
          $charArray = mb_str_split(mb_strtolower($str));
          $pattern = '';
          foreach ($charArray as $char) {
              $found = false;
              foreach ($replacements as $base => $regex) {
                  if (mb_strpos($regex, $char) !== false || $char === $base) {
                      $pattern .= $regex;
                      $found = true;
                      break;
                  }
              }
              if (!$found) {
                  $pattern .= preg_quote($char);
              }
          }
          return $pattern;
      };

      $regexPattern = $buildRegexPattern($search);
      
      // Try to find products by name or description first
      $query->where(function($q) use ($regexPattern) {
        $q->where('name', 'REGEXP', $regexPattern)
          ->orWhere('descr', 'REGEXP', $regexPattern);
      });

      // If no products found, try searching by package name
      if ($query->count() === 0) {
        // Reset query to find all products in packages that match the search term
        $query = GBProduct::whereHas('package', function ($q) use ($group, $regexPattern) {
          $q->where('group_id', $group->id)
            ->where('status', true)
            ->where('name', 'REGEXP', $regexPattern);
        })->where('status', true);
      }
    }

    // Apply package_id filter if it's not global search (package_id > 0)
    // and if there's no search (or if we want to respect tab even with search, 
    // but the user's previous request was to search all packages if searching)
    // The frontend currently sends package_id: 0 if search is active.
    if (isset ($payload['package_id']) && $payload['package_id'] > 0 && (!isset($payload['search']) || $payload['search'] === '')) {
      $query->where('package_id', $payload['package_id']);
    }

    $data = $query->orderBy('priority', 'desc')->get();

    return response()->json([
      'data'    => [
        'data' => $data,
      ],
      'status'  => 200,
      'message' => 'Lấy danh sách sản phẩm thành công',
    ], 200);

  }

  public function buy(Request $request, $code)
  {
    $product = GBProduct::with('package')->where('code', $code)->first();

    if ($product === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy thông tin sản phẩm này',
      ], 400);
    }

    $payload = $request->validate([
      'order_note'    => 'nullable|string',
      'input_user'    => 'required|string',
      'input_pass'    => 'required|string',
      'input_extra'   => 'nullable|string',
      'input_contact' => 'nullable|string',
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

    if (!is_numeric($product->price) || $product->price <= 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không thể tính tiền, vui lòng thử lại',
      ], 400);
    }

    if ($user->balance < $product->price) {
      $require = $product->price - $user->balance;

      return response()->json([
        'status'  => 400,
        'message' => 'Bạn còn thiếu ' . Helper::formatCurrency($require) . ' để mua!',
      ], 400);
    }

    if (!$user->decrement('balance', $product->price)) {
      return response()->json([
        'status'  => 400,
        'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
      ], 400);
    }

    $code = 'GB-' . Helper::randomString(8, true);

    $warrantyHours = 0;
    if (isset($product->warranty_hours) && $product->warranty_hours > 0) {
      $warrantyHours = $product->warranty_hours;
    } elseif (isset($product->package->warranty_hours) && $product->package->warranty_hours > 0) {
      $warrantyHours = $product->package->warranty_hours;
    }
    $warrantyExpireAt = $warrantyHours > 0 ? now()->addHours($warrantyHours) : null;

    $order = GBOrder::create([
      'name'        => $product->name,
      'code'        => $code,
      'input_user'    => $payload['input_user'],
      'input_pass'    => $payload['input_pass'],
      'input_extra'   => $payload['input_extra'] ?? '',
      'input_contact' => $payload['input_contact'] ?? '-',
      'payment'       => $product->price,
      'status'      => 'Pending',
      'user_id'     => $user->id,
      'username'    => $user->username,
      'package_id'  => $product->package_id,
      'group_id'    => $product->package->group_id,
      'order_note'  => $payload['order_note'] ?? '',
      'domain'      => Helper::getDomain(),
      'buyer_ip'    => $request->ip(),
      'buyer_ua'    => $request->userAgent(),
      'warranty_expire_at' => $warrantyExpireAt,
    ]);

    $product->package->update([
      'sold_count' => $product->package->sold_count + 1,
    ]);

    $group = isset ($product->package->group) ? $product->package->group->name : '-';

    $user->transactions()->create([
      'code'           => $code,
      'amount'         => $product->price,
      'balance_after'  => $user->balance,
      'balance_before' => $user->balance + $product->price,
      'domain'         => Helper::getDomain(),
      'type'           => 'boosting-buy',
      'extras'         => [
        'group_id'   => $product->package->group_id,
        'package_id' => $product->package_id,
      ],
      'status'         => 'paid',
      'content'        => 'Thuê gói cày ' . $product->name . '; Nhóm ' . $group,
      'user_id'        => $user->id,
      'username'       => $user->username,
    ]);


    try {
      $ref = $user->referrer;
      if ($ref !== null) {
        $affiliate = $ref->affiliate;
        if ($affiliate !== null) {
          $affiliate->increment('total_boost_buy');
        }
      }

      Helper::sendMessageTelegram("🎮🎮🎮 ĐƠN HÀNG CÀY THUÊ 🎮🎮🎮\nMã đơn: " . $order->code . "\nDịch vụ: " . $order->name . "\nThanh toán: " . Helper::formatCurrency($order->payment) . "\nTài khoản: " . $user->username . "\nGhi chú: " . $order->order_note . "\nThời gian: " . $order->created_at . "\n");

      Helper::sendEmailTemplate('order_boosting_created', $user->email, [
        'username' => $user->username,
        'name' => $order->name,
        'count' => 1,
        'code' => $order->code,
        'note' => $order->order_note,
        'payment' => Helper::formatCurrency($order->payment),
        'time' => $order->created_at,
        'title' => config('app.name'),
      ], [setting('email')]);
    } catch (\Exception $e) {
      // loi
    }

    Helper::updateCommission($user->id, $product->price, 'order', $code);

    // Notification
    \App\Models\Notification::create([
        'user_id' => $user->id,
        'type'    => 'order',
        'title'   => 'Thuê cày thuê thành công',
        'content' => 'Đơn hàng cày thuê ' . $product->name . ' (Mã: ' . $code . ') đã được tạo thành công.',
        'icon'    => 'fa fa-gamepad ps-1',
        'link'    => route('account.orders.boosting', $code), // Need route check? Yes, existing boosting view
        'is_read' => false
    ]);

    return response()->json([
      'data'    => [
        'code'    => $code,
        'name'    => $product->name,
        'payment' => $product->price,
      ],
      'status'  => 200,
      'message' => 'Tạo đơn hàng thành công, vui lòng đợi',
    ], 200);
  }

  public function buyMultiple(Request $request)
  {
    $payload = $request->validate([
      'product_codes'   => 'required|array|min:1',
      'product_codes.*' => 'required|string',
      'order_note'      => 'nullable|string',
      'input_user'      => 'required|string',
      'input_pass'      => 'required|string',
      'input_extra'     => 'nullable|string',
      'input_contact'   => 'nullable|string',
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

    $products = GBProduct::with('package')->whereIn('code', $payload['product_codes'])->get();

    if ($products->count() === 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy sản phẩm nào',
      ], 400);
    }

    $totalPrice = $products->sum('price');
    $coupon = null;
    $discountAmount = 0;

    if ($request->has('coupon_code') && $request->coupon_code) {
        $coupon = \App\Models\Coupon::where('coupon_code', $request->coupon_code)->first();
        if ($coupon) {
             // Use the group ID of the first product (assuming all from same group as per frontend logic)
             $firstProduct = $products->first();
             // Product ID for checking: "boosting-{group_id}"
            $productIds = ['boosting-' . $firstProduct->package->group_id];
            
            if ($coupon->isApplicable($totalPrice, $productIds)) {
                $discountAmount = $coupon->calculateDiscount($totalPrice);
                $totalPrice = max(0, $totalPrice - $discountAmount);
            } else {
                 return response()->json([
                    'status'  => 400,
                    'message' => 'Mã giảm giá không hợp lệ hoặc không áp dụng cho sản phẩm này.',
                 ], 400);
            }
        } else {
             return response()->json([
                'status'  => 400,
                'message' => 'Mã giảm giá không tồn tại.',
             ], 400);
        }
    }

    if ($totalPrice <= 0 && $products->sum('price') > 0) {
        // If discounted to 0 but original was > 0.
        // Bypassing "cannot calculate money" check because 0 is valid for 100% discount.
    } elseif ($totalPrice <= 0 && $products->sum('price') <= 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không thể tính tiền, vui lòng thử lại',
      ], 400);
    }
    
    // Check balance
    if ($user->balance < $totalPrice) {
      $require = $totalPrice - $user->balance;
      return response()->json([
        'status'  => 400,
        'message' => 'Bạn còn thiếu ' . Helper::formatCurrency($require) . ' để mua!',
      ], 400);
    }

    if (!$user->decrement('balance', $totalPrice) && $totalPrice > 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
      ], 400);
    }
    
    // Increment coupon usage
    if ($coupon) {
        $coupon->increment('used');
    }

    $code = 'GB-' . Helper::randomString(8, true);
    $productNames = $products->pluck('name')->join(', ');
    $firstProduct = $products->first();

    $warrantyHours = 0;
    // For multiple products, we'll use the minimum warranty found among selected products as a safe default
    $minWarranty = $products->min(function($p) {
        if (isset($p->warranty_hours) && $p->warranty_hours > 0) return $p->warranty_hours;
        return $p->package->warranty_hours ?? 0;
    });
    
    if ($minWarranty > 0) {
        $warrantyHours = $minWarranty;
    }

    $warrantyExpireAt = $warrantyHours > 0 ? now()->addHours($warrantyHours) : null;
    
    $order = GBOrder::create([
      'name'        => $productNames,
      'code'        => $code,
      'input_user'    => $payload['input_user'],
      'input_pass'    => $payload['input_pass'],
      'input_extra'   => $payload['input_extra'] ?? '',
      'input_contact' => $payload['input_contact'] ?? '-',
      'payment'       => $totalPrice,
      'status'      => 'Pending',
      'user_id'     => $user->id,
      'username'    => $user->username,
      'package_id'  => $firstProduct->package_id,
      'group_id'    => $firstProduct->package->group_id,
      'order_note'  => $payload['order_note'] ?? '',
      'domain'      => Helper::getDomain(),
      'buyer_ip'    => $request->ip(),
      'buyer_ua'    => $request->userAgent(),
      'warranty_expire_at' => $warrantyExpireAt,
      'extras'      => [
        'products' => $products->map(function($p) {
          return [
            'code' => $p->code,
            'name' => $p->name,
            'price' => $p->price,
          ];
        })->toArray(),
        'coupon_code'=> $coupon ? $coupon->coupon_code : null,
        'discount'   => $discountAmount
      ]
    ]);

    foreach ($products as $product) {
      $product->package->update([
        'sold_count' => $product->package->sold_count + 1,
      ]);
    }

    $group = isset($firstProduct->package->group) ? $firstProduct->package->group->name : '-';

    $user->transactions()->create([
      'code'           => $code,
      'amount'         => $totalPrice,
      'balance_after'  => $user->balance,
      'balance_before' => $user->balance + $totalPrice,
      'domain'         => Helper::getDomain(),
      'type'           => 'boosting-buy',
      'extras'         => [
        'group_id'   => $firstProduct->package->group_id,
        'package_id' => $firstProduct->package_id,
        'products'   => $products->pluck('code')->toArray(),
        'coupon_code'=> $coupon ? $coupon->coupon_code : null,
        'discount'   => $discountAmount
      ],
      'status'         => 'paid',
      'content'        => 'Thuê ' . $products->count() . ' gói cày; Nhóm ' . $group . ($coupon ? ' (Giảm giá: ' . Helper::formatCurrency($discountAmount) . ')' : ''),
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

      Helper::sendEmailTemplate('order_boosting_created', $user->email, [
        'username' => $user->username,
        'name' => $productNames,
        'count' => $products->count(),
        'code' => $order->code,
        'note' => $order->order_note,
        'payment' => Helper::formatCurrency($order->payment),
        'time' => $order->created_at,
        'title' => config('app.name'),
      ], [setting('email')]);
    } catch (\Exception $e) {
      // loi
    }

    Helper::updateCommission($user->id, $totalPrice, 'order', $code);

    // Notification
    \App\Models\Notification::create([
        'user_id' => $user->id,
        'type'    => 'order',
        'title'   => 'Thuê cày thuê thành công',
        'content' => 'Bạn đã thuê thành công ' . $products->count() . ' gói cày thuê (Mã đơn: ' . $code . ').',
        'icon'    => 'fa fa-gamepad ps-1',
        'link'    => route('account.orders.boosting', $code),
        'is_read' => false
    ]);

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
// TEST_POWERSHELL_APPEND
