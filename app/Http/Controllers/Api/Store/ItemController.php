<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\ItemData;
use App\Models\ItemGroup;
use App\Models\ItemOrder;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class ItemController extends Controller
{
  public function index(Request $request)
  {
    $payload    = $request->validate([
      'page'       => 'nullable|integer',
      'limit'      => 'nullable|integer',
      'price'      => 'nullable|string',
      'search'     => 'nullable|string',
      'sort_by'    => 'nullable|string',
      'group_id'   => 'required|integer',
      'package_id' => 'nullable|integer',
      'sort_type'  => 'nullable|string|in:asc,desc',
      'display_by' => 'nullable|string|in:created_at_asc,created_at_desc,price_asc,price_desc,priority_asc,priority_desc',
    ]);
    $page       = $payload['page'] ?? 1;
    $limit      = $payload['limit'] ?? 10;
    $search     = $payload['search'] ?? null;
    $offset     = ($page - 1) * $limit;
    $sort_by    = $payload['sort_by'] ?? 'id';
    $sort_type  = $payload['sort_type'] ?? 'desc';
    $display_by = $payload['display_by'] ?? null;

    $group = ItemGroup::where('id', $payload['group_id'])->where('status', true)->first();

    if ($group === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy nhóm dịch vụ này',
      ], 400);
    }

    $query = $group->data()->where('status', true);

    if (isset($payload['package_id']) && !empty($payload['package_id'])) {
      $query = $query->whereHas('packages', function($q) use ($payload) {
          $q->where('item_packages.id', $payload['package_id']);
      });
    }

    if (isset($search)) {
      if (is_numeric($search)) {
        $query = $query->where('code', $search);
      } else {
        $query = $query->where(function ($q) use ($search) {
          $q->where('name', 'like', '%' . $search . '%')
            ->orWhere('code', 'like', '%' . $search . '%');
        });
      }
    }

    if (isset($payload['sort_by'])) {
      $query = $query->orderBy($sort_by, $sort_type);
    }

    if (isset($payload['price'])) {
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

    if ($display_by !== null) {
      if ($display_by === 'created_at_asc') {
        $query = $query->orderBy('created_at', 'asc');
      } else if ($display_by === 'created_at_desc') {
        $query = $query->orderBy('created_at', 'desc');
      } else if ($display_by === 'price_asc') {
        $query = $query->orderBy('price', 'asc');
      } else if ($display_by === 'price_desc') {
        $query = $query->orderBy('price', 'desc');
      } else if ($display_by === 'priority') {
        $query = $query->orderBy('priority', 'asc');
      } else if ($display_by === 'priority_desc') {
        $query = $query->orderBy('priority', 'desc');
      }
    } else {
      $query = $query->orderBy('priority', 'desc')->orderBy($sort_by, $sort_type);
    }

    $data = $query->skip($offset)
      ->take($limit)
      ->get();

    return response()->json([
      'data'    => [
        'meta' => $meta,
        'data' => $data,
      ],
      'status'  => 200,
      'message' => 'Lấy danh sách vật phẩm thành công',
    ], 200);
  }

  public function show($code)
  {
    $item = ItemData::where('code', $code)->first();

    if ($item === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy sản phẩm này',
      ], 400);
    }

    if ($item->is_sold === true) {
      return response()->json([
        'status'  => 400,
        'message' => 'Sản phẩm này đã được bán',
      ], 400);
    }

    return response()->json([
      'data'    => $item,
      'status'  => 200,
      'message' => 'Lấy thông tin vật phẩm thành công',
    ], 200);
  }

  public function buy(Request $request, $code)
  {
    $payload = [];
    if (str_starts_with($code, 'robux-')) {
        return $this->buyRobuxPackage($request, $code);
    }

    $item = ItemData::where('code', $code)->where('status', true)->first();

    if ($item === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy sản phẩm này',
      ], 400);
    }

    $user = User::find($request->user()?->id);

    if ($user === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không xác thực được thông tin người dùng',
      ], 400);
    }

    if ($item->type === 'user') {
      $payload = $request->validate([
        'user_note' => 'nullable|string|max:255',
        'Tai_Khoan' => 'required|string',
        'Lien_He'   => 'nullable|string',
      ], ['required' => ':attribute không được để trống'], ['Tai_Khoan' => 'Tài khoản']);
    } else if ($item->type === 'user_pass') {
      $message      = [
        'required' => 'Vui lòng nhập :attribute',
        'string'   => ':attribute phải là chuỗi',
      ];
      $attributes   = [
        'Lien_He'        => 'Liên hệ',
        'Mat_Khau'       => 'Mật khẩu',
        'Tai_Khoan'      => 'Tài khoản',
      ];
      $DangNhapBang = ["Riot", "Garena", "Steam", "Facebook", "Google", "Roblox", "Other"];

      if (is_array($item->group->login_with)) {
        $DangNhapBang = $item->group->login_with ?? $DangNhapBang;
      }

      $payload = $request->validate([
        'Lien_He'        => 'nullable|string',
        'Ten_Game'       => 'nullable|string',
        'Mat_Khau'       => 'required|string',
        'Tai_Khoan'      => 'required|string',
        'user_note'      => 'nullable|string|max:255',
        'Dang_Nhap_Bang' => 'nullable|string',
      ], $message, $attributes);
    } else if ($item->type === 'gamepass') {
      $payload = $request->validate([
          'Tai_Khoan' => 'required|string',
          'user_note' => 'nullable|string',
          'Lien_He'   => 'nullable|string',
      ], [
          'Tai_Khoan.required' => 'Vui lòng nhập Link GamePass'
      ]);
    } else {
        $payload = $request->validate([
            'Tai_Khoan' => 'nullable|string',
            'user_note' => 'nullable|string',
            'Lien_He'   => 'nullable|string',
        ]);
    }

    // ... valid user check
    
    // Validate inputs
    if ($item->type === 'user') {
       // ...
    } else if ($item->type === 'user_pass') {
       // ...
    }

    $finalPayment = $item->payment;
    $coupon = null;
    $discountAmount = 0;

    if ($request->has('coupon_code') && $request->coupon_code) {
        $coupon = \App\Models\Coupon::where('coupon_code', $request->coupon_code)->first();
        if ($coupon) {
             // Product ID for checking: "item-{group_id}"
            $productIds = ['item-' . $item->group_id];
            
            if ($coupon->isApplicable($item->payment, $productIds)) {
                $discountAmount = $coupon->calculateDiscount($item->payment);
                $finalPayment = max(0, $item->payment - $discountAmount);
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

    if (!is_numeric($finalPayment) || $finalPayment < 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không thể tính tiền, vui lòng thử lại',
      ], 400);
    }
    
    // Only check free wait time if original payment was 0. A discounted free item shouldn't trigger this?
    // Following previous logic: ensure we check original item payment.
    if ($item->payment === 0) {
      $timeWait   = setting('time_wait_free', 10); // seconds
      $lastAction = $user->last_action; // timestamp

       // ... wait logic ...
       if ($lastAction !== null) {
        $timeDiff = now()->diffInSeconds($lastAction);

        if ($timeDiff < $timeWait) {
          return response()->json([
            'status'  => 400,
            'message' => __t('Bạn cần chờ') . ' ' . ($timeWait - $timeDiff) . ' ' . __t('giây để mua tài khoản miễn phí'),
          ], 400);
        }
      }

      $user->update([
        'last_action' => now(),
      ]);
    }

    if ($user->balance < $finalPayment) {
      $require = $finalPayment - $user->balance;

      return response()->json([
        'status'  => 400,
        'message' => 'Bạn còn thiếu ' . Helper::formatCurrency($require) . ' để mua!',
      ], 400);
    }

    if (!$user->decrement('balance', $finalPayment) && $finalPayment > 0) {
      return response()->json([
        'status'  => 400,
        'message' => __t('Không thể trừ tiền, vui lòng thử lại'),
      ], 400);
    }
    
    // Increment coupon usage
    if ($coupon) {
        $coupon->increment('used');
    }

    $item->update([
      'sold_count' => $item->sold_count + 1,
    ]);
    
    // Add coupon info to payload/extra_data
    if ($coupon) {
        $payload['coupon_code'] = $coupon->coupon_code;
        $payload['discount_amount'] = $discountAmount;
    }

    $warrantyHours = 0;
    if (isset($item->warranty_hours) && $item->warranty_hours > 0) {
      $warrantyHours = $item->warranty_hours;
    } elseif (isset($item->group->warranty_hours) && $item->group->warranty_hours > 0) {
      $warrantyHours = $item->group->warranty_hours;
    }

    $warrantyExpireAt = $warrantyHours > 0 ? now()->addHours($warrantyHours) : null;

    $order = ItemOrder::create([
      'code'           => 'OG-' . Helper::randomString(8, true),
      'type'           => $item->type,
      'name'           => $item->name,
      'data'           => [
        'id' => $item->id,
      ],
      'robux'          => $item->robux,
      'robox_rate'     => setting('rate_robux', 100),
      'payment'        => $finalPayment,
      'discount'       => $item->discount,
      'status'         => 'Pending',
      'input_user'     => $payload['Tai_Khoan'] ?? '-',
      'input_pass'     => $payload['Mat_Khau'] ?? '-',
      'input_auth'     => $payload['Dang_Nhap_Bang'] ?? '-',
      'input_contact'  => $payload['Lien_He'] ?? '-',
      'user_id'        => $user->id,
      'username'       => $user->username,
      'domain'         => Helper::getDomain(),
      'admin_note'     => '',
      'order_note'     => $payload['user_note'] ?? '',
      'extra_data'         => $payload,
      'buyer_ip'           => $request->ip(),
      'buyer_ua'           => $request->userAgent(),
      'warranty_expire_at' => $warrantyExpireAt,
    ]);

    $user->transactions()->create([
      'code'           => $order->code,
      'amount'         => $finalPayment,
      'balance_after'  => $user->balance,
      'balance_before' => $user->balance + $finalPayment,
      'domain'         => Helper::getDomain(),
      'type'           => 'item-buy',
      'extras'         => [
        'group_id'   => $item->group_id,
        'account_id' => $item->id,
        'coupon_code'=> $coupon ? $coupon->coupon_code : null,
        'discount'   => $discountAmount
      ],
      'status'         => 'paid',
      'content'        => 'Mua dịch vụ ' . $item->name . '; Nhóm ' . $item->group->name . ($coupon ? ' (Giảm giá: ' . Helper::formatCurrency($discountAmount) . ')' : ''),
      'user_id'        => $user->id,
      'username'       => $user->username,
    ]);

    try {
      $ref = $user->referrer;
      if ($ref !== null) {
        $affiliate = $ref->affiliate;
        if ($affiliate !== null) {
          $affiliate->increment('total_item_buy');
        }
      }

      Helper::sendMessageTelegram("📦📦📦 ĐƠN HÀNG VẬT PHẨM 📦📦📦\nMã đơn: " . $order->code . "\nDịch vụ: " . $order->name . "\nThanh toán: " . Helper::formatCurrency($order->payment) . "\nTài khoản: " . $user->username . "\nGhi chú: " . $order->order_note . "\nThời gian: " . $order->created_at . "\n");

      Helper::sendEmailTemplate('order_item_created', $user->email, [
        'username' => $user->username,
        'name' => $order->name,
        'code' => $order->code,
        'payment' => Helper::formatCurrency($order->payment),
        'note' => $order->order_note, // Although not always present, good to have
        'time' => $order->created_at,
        'title' => config('app.name'),
      ], [setting('email')]);

    } catch (\Exception $e) {
      // loi
    }

    Helper::updateCommission($user->id, $finalPayment, 'order');

    // Notification
    \App\Models\Notification::create([
        'user_id' => $user->id,
        'type'    => 'order',
        'title'   => 'Đặt hàng thành công',
        'content' => 'Đơn hàng dịch vụ ' . $order->name . ' (Mã: ' . $order->code . ') đã được tạo thành công.',
        'icon'    => 'fa fa-shopping-bag ps-1',
        'link'    => route('account.orders.items', $order->code),
        'is_read' => false
    ]);

    return response()->json([
      'data'    => [
        'code' => $order->code,
      ],
      'status'  => 200,
      'message' => 'Đặt hàng thành công, vui lòng đợi xử lý',
    ], 200);
  }

  private function buyRobuxPackage(Request $request, $code)
  {
      $parts = explode('-', $code); // robux-{service_id}-{index}
      if (count($parts) < 3) {
          return response()->json(['status' => 400, 'message' => 'Mã gói không hợp lệ'], 400);
      }

      $serviceId = $parts[1];
      $index = (int)$parts[2];

      $service = \App\Models\ServiceCategory::find($serviceId);
      if (!$service || $service->product_type !== 'robux') {
           return response()->json(['status' => 400, 'message' => 'Dịch vụ không tồn tại'], 400);
      }

      $prizes = $service->prizes;
      
      if ($service->robux_type === 'genuine') {
          $robuxAmount = 0;
          $price = 0;

          if ($request->has('package_indices') && is_array($request->package_indices) && count($request->package_indices) > 0) {
              foreach ($request->package_indices as $idx) {
                  if (isset($prizes[$idx])) {
                      $p = $prizes[$idx];
                      $robuxAmount += (int)str_replace(['.', ','], '', (string)($p['value'] ?? 0));
                      $price += (int)str_replace(['.', ','], '', (string)($p['percent'] ?? 0));
                  }
              }
          } elseif ($request->has('robux_amount') && (int)$request->robux_amount > 0) {
              $robuxAmount = (int)$request->robux_amount;
              // Try to find exact match in prizes
              $found = false;
              foreach ($prizes as $package) {
                  $val = (int)str_replace(['.', ','], '', (string)($package['value'] ?? 0)); 
                  if ($val === $robuxAmount) {
                      $price = (int)str_replace(['.', ','], '', (string)($package['percent'] ?? 0));
                      $found = true;
                      break;
                  }
              }
              if (!$found) {
                  return response()->json(['status' => 400, 'message' => 'Số lượng Robux không hợp lệ cho gói này'], 400);
              }
          } else {
              // Fallback to single index from code
              if (!isset($prizes[$index])) {
                   return response()->json(['status' => 400, 'message' => 'Gói Robux không tồn tại'], 400);
              }
              $package = $prizes[$index];
              $robuxAmount = (int)str_replace(['.', ','], '', (string)($package['value'] ?? 0)); 
              $price = (int)str_replace(['.', ','], '', (string)($package['percent'] ?? 0));
          }
      } else {
          // Standard rate-based logic for 120h
          if ($request->has('robux_amount') && (int)$request->robux_amount > 0) {
              $robuxAmount = (int)$request->robux_amount;
              $rawRate = $service->price > 0 ? $service->price : setting('rate_robux', 10);
              $rate = $this->getRobuxRate($robuxAmount, $rawRate);
              $price = $robuxAmount * $rate;
          } else {
              if (!isset($prizes[$index])) {
                   return response()->json(['status' => 400, 'message' => 'Gói Robux không tồn tại'], 400);
              }
              $package = $prizes[$index];
              $robuxAmount = (int)str_replace(['.', ','], '', (string)($package['value'] ?? 0)); 
              $price = (int)str_replace(['.', ','], '', (string)($package['percent'] ?? 0));
              if ($price <= 0) {
                  $rawRate = $service->price > 0 ? $service->price : setting('rate_robux', 10);
                  $rate = $this->getRobuxRate($robuxAmount, $rawRate);
                  $price = $robuxAmount * $rate;
              }
          }
      }

      $finalPayment = $price;

      // Validation
      $request->validate([
          'Tai_Khoan' => 'required|string', // Gamepass Link
          'Mat_Khau'  => 'nullable|string',
          'user_note' => 'nullable|string'
      ], [
          'Tai_Khoan.required' => 'Vui lòng nhập Link Gamepass'
      ]);

      $user = User::find($request->user()?->id);
      if (!$user) return response()->json(['status' => 400, 'message' => 'Không xác thực được người dùng'], 400);

      // Coupon Logic
      $coupon = null;
      $discountAmount = 0;

      if ($request->has('coupon_code') && $request->coupon_code) {
        $coupon = \App\Models\Coupon::where('coupon_code', $request->coupon_code)->first();
        if ($coupon) {
            // Check against generic service-id or global
            $productIds = ['item-' . $serviceId]; 
            
            if ($coupon->isApplicable($finalPayment, $productIds)) {
                $discountAmount = $coupon->calculateDiscount($finalPayment);
                $finalPayment = max(0, $finalPayment - $discountAmount);
            } else {
                 return response()->json(['status' => 400, 'message' => 'Mã giảm giá không hợp lệ.'], 400);
            }
        } else {
             return response()->json(['status' => 400, 'message' => 'Mã giảm giá không tồn tại.'], 400);
        }
      }

      // Balance Check
      if ($user->balance < $finalPayment) {
          return response()->json(['status' => 400, 'message' => 'Bạn còn thiếu ' . Helper::formatCurrency($finalPayment - $user->balance) . ' để thanh toán'], 400);
      }

      // Deduct
      if (!$user->decrement('balance', $finalPayment) && $finalPayment > 0) {
          return response()->json(['status' => 400, 'message' => 'Lỗi trừ tiền'], 400);
      }

      if ($coupon) $coupon->increment('used');

      $warrantyHours = 0;
      if (isset($service->warranty_hours) && $service->warranty_hours > 0) {
          $warrantyHours = $service->warranty_hours;
      }

      $warrantyExpireAt = $warrantyHours > 0 ? now()->addHours($warrantyHours) : null;

      // Create Order
      $order = ItemOrder::create([
          'code'           => 'OG-' . Helper::randomString(8, true),
          'type'           => 'robux',
          'name'           => "Gói {$robuxAmount} Robux",
          'data'           => ['service_id' => $serviceId],
          'robux'          => $robuxAmount,
          'rate_robux'     => 100,
          'payment'        => $finalPayment,
          'discount'       => $discountAmount,
          'status'         => 'Pending',
          'input_user'     => $request->Tai_Khoan,
          'input_pass'     => $request->Mat_Khau ?? '-',
          'user_id'        => $user->id,
          'username'       => $user->username,
          'domain'         => Helper::getDomain(),
          'admin_note'     => '',
          'order_note'     => $request->user_note,
          'extra_data'     => [
                'coupon_code' => $coupon ? $coupon->coupon_code : null, 
                'discount_amount' => $discountAmount
            ],
          'buyer_ip'           => $request->ip(),
          'buyer_ua'           => $request->userAgent(),
          'warranty_expire_at' => $warrantyExpireAt,
      ]);

      // Transaction
      $user->transactions()->create([
          'code'           => $order->code,
          'amount'         => $finalPayment,
          'balance_after'  => $user->balance,
          'balance_before' => $user->balance + $finalPayment,
          'domain'         => Helper::getDomain(),
          'type'           => 'item-buy',
          'extras'         => [
             'service_id' => $serviceId,
             'coupon_code' => $coupon ? $coupon->coupon_code : null
          ],
          'status'         => 'paid',
          'content'        => "Mua gói {$robuxAmount} Robux",
          'user_id'        => $user->id,
          'username'       => $user->username
      ]);
      
      Helper::updateCommission($user->id, $finalPayment, 'order');
      
       // Notify (Email/Telegram/DB) - Simplified call
       try {
           Helper::sendMessageTelegram("📦 Mua Robux: {$order->code} - {$order->name} - " . Helper::formatCurrency($order->payment));
       } catch (\Exception $e) {}

      return response()->json([
          'data' => ['code' => $order->code],
          'status' => 200,
          'message' => 'Đặt đơn thành công!'
      ]);
  }

  private function getRobuxRate($amount, $config)
  {
      // If config is a simple number, return it
      if (is_numeric($config)) {
          return (float)$config;
      }

      $config = (string)$config;
      if (empty($config)) return 0;

      $parts = explode(',', $config);
      $tiers = [];

      foreach ($parts as $part) {
          $s = trim($part);
          if (empty($s)) continue;

          if (strpos($s, '|') !== false) {
              [$limit, $r] = array_map('intval', explode('|', $s));
              if ($r > 0) {
                  $tiers[] = ['limit' => $limit, 'rate' => $r];
              }
          } else {
              $r = (int)$s;
              if ($r > 0) {
                  $tiers[] = ['limit' => 0, 'rate' => $r];
              }
          }
      }

      // Sort by limit ASC
      usort($tiers, function ($a, $b) {
          return $a['limit'] <=> $b['limit'];
      });

      // Find applicable rate (highest limit <= amount)
      $applicableRate = 0;
      if (count($tiers) > 0) {
          $applicableRate = $tiers[0]['rate']; // Default to lowest tier
          foreach ($tiers as $tier) {
              if ($amount >= $tier['limit']) {
                  $applicableRate = $tier['rate'];
              }
          }
      }

      return $applicableRate > 0 ? $applicableRate : (float)$config; // Fallback
  }
}
