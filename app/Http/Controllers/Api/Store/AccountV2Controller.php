<?php

namespace App\Http\Controllers\Api\Store;

use App\Http\Controllers\Controller;
use App\Models\BulkOrder;
use App\Models\GroupV2;
use App\Models\ResourceV2;
use App\Models\ResourceV2O;
use App\Models\ListItemV2;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AccountV2Controller extends Controller
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

    $group = GroupV2::where('id', $payload['group_id'])->where('status', true)->first();

    if ($group === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy nhóm dịch vụ này',
      ], 400);
    }

    $query = $group->items()->where('status', true);

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

    $data = $data->map(function ($item) {
      $item->makeHidden(['list_image', 'description']);
      return $item;
    });

    return response()->json([
      'data'    => [
        'meta' => $meta,
        'data' => $data,
      ],
      'status'  => 200,
      'message' => 'Lấy danh sách tài khoản thành công',
    ], 200);
  }

  public function show($code)
  {
    $item = ListItemV2::where('code', $code)->first();

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
      'message' => 'Lấy thông tin tài khoản thành công',
    ], 200);
  }

  public function buy(Request $request, $code)
  {
    $payload  = $request->validate([
      'quantity' => 'nullable|integer',
    ]);
    $quantity = $payload['quantity'] ?? 1;

    $item = ListItemV2::where('code', $code)->first();

    if ($item === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy thông tin sản phẩm này',
      ], 400);
    }

    if ($item->status !== true) {
      return response()->json([
        'status'  => 400,
        'message' => 'Sản phẩm này hiện đã bị vô hiệu hoá',
      ], 400);
    }

    $group = $item->group;

    if ($group === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy thông tin nhóm dịch vụ',
      ], 400);
    }

    if (!$group->status) {
      return response()->json([
        'status'  => 400,
        'message' => 'Nhóm dịch vụ này đã bị vô hiệu hoá',
      ], 400);
    }

    // if (!feature_enabled('bulk-orders')) {
    //   $quantity = 1;
    // }

    if ($quantity > 1 && $item->is_bulk === 1) {
      return response()->json([
        'status'  => 400,
        'message' => 'Sản phẩm này không hỗ trợ mua số lượng lớn',
      ], 400);
    }

    if ($item->is_bulk > 1 && $quantity > $item->is_bulk) {
      return response()->json([
        'status'  => 400,
        'message' => 'Sản phẩm này chỉ hỗ trợ mua tối đa ' . $item->is_bulk . ' tài khoản',
      ], 400);
    }

    // check resources available
    $resources = collect([]);
    $isPreorder = false;
    
    if ($item->client_type !== 'api') {
        $resources = $item->resources()->where('buyer_name', null)
          ->where('buyer_code', null)
          ->limit($quantity)->get();

        if ($resources->count() < $quantity) {
            if ($item->allow_preorder) {
                // Pre-order Logic: Create placeholder resources
                $needed = $quantity - $resources->count();
                $isPreorder = true;
                for ($i = 0; $i < $needed; $i++) {
                    $resources->push((object)['username' => 'PREORDER_WAITING', 'code' => $item->code, 'type' => $item->type ?? 'account']);
                }
            } else {
                return response()->json([
                  'status'  => 400,
                  'message' => 'Sản phầm này tạm hết hoặc không đủ số lượng cung cấp',
                ], 400);
            }
        }
    } else {
        // API Product: Check stock from API
        // API products are NOT allowed for pre-order - only buy when API has stock
        $apiAmount = $item->amount; // This uses getAmountAttribute()
        
        if ($apiAmount < $quantity) {
            // API không đủ hàng - từ chối mua (không cho preorder)
            return response()->json([
              'status'  => 400,
              'message' => 'Sản phẩm từ API này tạm hết hàng, vui lòng quay lại sau',
            ], 400);
        } else {
            // API has enough stock - will try to buy below
            for ($i = 0; $i < $quantity; $i++) {
                $resources->push((object)['username' => 'PREORDER_WAITING', 'code' => $item->code, 'type' => $item->type ?? 'account']);
            }
        }
    }

    // $resource = $item->resources()->where('buyer_name', null)
    //   ->where('buyer_code', null)
    //   ->limit($quantity)->get();

    // if ($resource === null) {
    //   return response()->json([
    //     'status'  => 400,
    //     'message' => 'Sản phẩm này tạm hết tài khoản, vui lòng quay lại sau',
    //   ], 400);
    // }

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

    $totalPayment = $item->payment * $quantity;
    $coupon = null;
    $discountAmount = 0;

    if ($request->has('coupon_code') && $request->coupon_code) {
        $coupon = \App\Models\Coupon::where('coupon_code', $request->coupon_code)->first();
        if ($coupon) {
             // Product ID for checking: "account_v2-{group_id}"
            $productIds = ['account_v2-' . $item->group_id];
            
            if ($coupon->isApplicable($totalPayment, $productIds)) {
                if (!$coupon->isValidForUser($user->id)) {
                     return response()->json([
                        'status'  => 400,
                        'message' => 'Bạn đã hết lượt sử dụng mã giảm giá này.',
                     ], 400);
                }
                $discountAmount = $coupon->calculateDiscount($totalPayment);
                $totalPayment = max(0, $totalPayment - $discountAmount);
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

    if (!is_numeric($totalPayment) || $totalPayment < 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không thể tính tiền, vui lòng thử lại',
      ], 400);
    }

    if (!$totalPayment && $quantity === 1) { // Only free for single quantity logic? Original code implies free logic inside !totalPayment check
       // ... existing free check logic, but wait, original code checked 'if (!$totalPayment)'
       // If totalPayment became 0 due to discount, we should bypass the "free wait time" logic? 
       // Or should we keep it? 
       // The original code: if (!$totalPayment) { wait logic ... }
       // If I discount to 0, it enters here.
       // Usually coupons making it 0 is fine, no wait needed?
       // But let's keep it safe. If it is 0, we check wait.
       
       // ... copy existing wait logic ...
       // Actually, the original code had:
       /*
        if (!$totalPayment) {
          $timeWait = ...
          ...
        }
       */
       // If I put this AFTER discount calc, then a 100% discount coupon triggers the wait timer.
       // Maybe I should check if ORIGINAL payment was 0?
       // Original code used $item->payment * $quantity.
       // If $item->payment > 0, then original totalPayment > 0.
       // If discount makes it 0, we should probably SKIP the "free account wait" check, because it's a paid account made free by coupon.
       // The "free account wait" is usually for "giveaway" accounts (price=0 in DB).
       // So I should check `$item->payment * $quantity` for the wait logic, NOT the discounted `$totalPayment`.
    }
    
    // Let's refactor:
    // 1. Calculate original total.
    $originalTotal = $item->payment * $quantity;
    
    if ($originalTotal == 0) {
        // ... wait logic ...
          $timeWait   = setting('time_wait_free', 10); // seconds
          $lastAction = $user->last_action; // timestamp
    
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

    // Check balance with discounted price
    if ($user->balance < $totalPayment) {
      $require = $totalPayment - $user->balance;
      return response()->json([
        'status'  => 400,
        'message' => __t('Bạn còn thiếu') . ' ' . Helper::formatCurrency($require) . ' ' . __t('để mua!'),
      ], 400);
    }

    if (!$user->decrement('balance', $totalPayment) && $totalPayment > 0) {
      return response()->json([
        'status'  => 400,
        'message' => __t('Không thể trừ tiền, vui lòng thử lại'),
      ], 400);
    }
    
    // Increment coupon usage
    if ($coupon) {
        $coupon->increment('used');
    }
    
    // API Purchase Logic - Only buy immediately if NOT pre-order
    if ($item->client_type === 'api' && !$isPreorder) {
        $apiDomain = $item->api_domain;
        $apiKey = $item->api_key;
        
        if ($item->api_config_id && $item->apiConfig) {
            $apiDomain = $item->apiConfig->url;
            $apiKey = $item->apiConfig->api_key;
        }

        if (!$apiDomain || !$apiKey || !$item->api_id) {
            $user->increment('balance', $totalPayment);
            if ($coupon) {
                $coupon->decrement('used');
            }
            return response()->json([
                'status' => 400,
                'message' => 'Cấu hình API sản phẩm không hợp lệ, vui lòng liên hệ admin',
            ], 400);
        }

        try {
             $targetApiId = $item->getAvailableApiProduct();

             if (!$targetApiId) {
                  // REFUND
                  $user->increment('balance', $totalPayment);
                  if ($coupon) {
                      $coupon->decrement('used');
                  }
                  return response()->json([
                      'status' => 400,
                      'message' => 'Sản phẩm từ API này tạm hết hàng, vui lòng quay lại sau',
                  ], 400);
             }

             $apiPayload = [
                 'action' => 'buyProduct',
                 'id' => $targetApiId,
                 'amount' => $quantity,
                 'api_key' => $apiKey,
             ];

             $apiCoupon = $item->api_coupon;
             if (empty($apiCoupon) && $item->api_config_id && $item->apiConfig) {
                 $apiCoupon = $item->apiConfig->coupon;
             }
             
             if ($apiCoupon) {
                 $apiPayload['coupon'] = $apiCoupon;
             }
             
             $response = Http::asForm()->post(rtrim($apiDomain, '/') . '/api/buy_product', $apiPayload);
             $apiResult = $response->json();
             
             if (isset($apiResult['status']) && $apiResult['status'] === 'success' && !empty($apiResult['data'])) {
                  $resources = collect([]);
                  foreach ($apiResult['data'] as $line) {
                      $res = new \App\Models\ResourceV2S();
                      $res->code = $item->code;
                      $res->type = $item->type ?? 'account';
                      $res->username = $line; // Save the FULL raw string as-is
                      $res->domain = Helper::getDomain();
                      $res->save();
                      
                      $resources->push($res);
                  }
                   // Update local cache to reflect purchase immediately
                  $cacheKey = 'api_product_' . $targetApiId . '_' . md5($apiDomain);
                  if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                      \Illuminate\Support\Facades\Cache::decrement($cacheKey, $quantity);
                  }
             } else {
                  // Refund
                  $user->increment('balance', $totalPayment);
                  if ($coupon) {
                      $coupon->decrement('used');
                  }
                  
                  return response()->json([
                      'status' => 400,
                      'message' => $apiResult['msg'] ?? $apiResult['message'] ?? 'Lỗi kết nối API đại lý (Mua hàng thất bại)',
                  ], 400);
             }
        } catch (\Exception $e) {
             $user->increment('balance', $totalPayment);
             if ($coupon) {
                  $coupon->decrement('used');
             }
             return response()->json(['status'=>400, 'message' => 'Lỗi hệ thống khi gọi API: ' . $e->getMessage()], 400);
        }
    }

    $warrantyHours = 0;
    if (isset($item->warranty_hours) && $item->warranty_hours > 0) {
      $warrantyHours = $item->warranty_hours;
    } elseif (isset($item->group->warranty_hours) && $item->group->warranty_hours > 0) {
      $warrantyHours = $item->group->warranty_hours;
    }

    $warrantyExpireAt = $warrantyHours > 0 ? now()->addHours($warrantyHours) : null;

    if ($quantity === 1) {
      $resource = $resources->first();

      if ($resource === null) {
        $user->increment('balance', $totalPayment);

        return response()->json([
          'status'  => 400,
          'message' => 'Có lỗi xảy ra, vui lòng thử lại sau',
        ], 400);
      }

      $code = 'Y2-' . Helper::randomString(8, true);
      
      // Determine order status based on whether this is a pre-order
      $isPreorder = ($resource->username === 'PREORDER_WAITING');
      $orderStatus = $isPreorder ? 'Processing' : 'Completed';

      // Create Order
      ResourceV2O::create([
        'code'               => $resource->code,
        'type'               => $resource->type,
        'domain'             => Helper::getDomain(),
        'is_bulk'            => false,
        'username'           => $resource->username,
        'buyer_code'         => $code,
        'buyer_name'         => $user->username,
        'buyer_paym'         => $totalPayment,
        'buyer_date'         => now(),
        'buyer_ip'           => $request->ip(),
        'buyer_ua'           => $request->userAgent(),
        'warranty_expire_at' => $warrantyExpireAt,
        'order_status'       => $orderStatus,
      ]);

      // Delete from inventory only if it's a real model (not a placeholder)
      if ($resource instanceof \App\Models\ResourceV2S) {
        $resource->delete();
      }

      $group = isset($item->group) ? $item->group->name : '-';

      $user->transactions()->create([
        'code'           => $code,
        'amount'         => $totalPayment,
        'cost_amount'    => $item->cost,
        'balance_after'  => $user->balance,
        'balance_before' => $user->balance + $totalPayment,
        'domain'         => Helper::getDomain(),
        'type'           => 'account-v2-buy',
        'extras'         => [
          'code'       => $item->code,
          'group_id'   => $item->group_id,
          'account_id' => $item->id,
          'coupon_code'=> $coupon ? $coupon->coupon_code : null,
          'discount'   => $discountAmount,
          'api_product_id' => $targetApiId ?? null
        ],
        'status'         => 'paid',
        'content'        => '[V2] Mua tài khoản #' . $item->code . '; Nhóm ' . $group . ($coupon ? ' (Giảm giá: ' . Helper::formatCurrency($discountAmount) . ')' : ''),
        'user_id'        => $user->id,
        'username'       => $user->username,
      ]);

      $ref = $user->referrer;
      if ($ref !== null) {
        $affiliate = $ref->affiliate;
        if ($affiliate !== null) {
          $affiliate->increment('total_account_buy');
        }
      }

      Helper::updateCommission($user->id, $totalPayment, 'order', $code);

      Helper::updateCommission($user->id, $totalPayment, 'order', $code);

      // Notification
      \App\Models\Notification::create([
          'user_id' => $user->id,
          'type'    => 'order',
          'title'   => 'Mua tài khoản thành công',
          'content' => 'Bạn đã mua thành công tài khoản mã số #' . $item->code . ' với giá ' . \Helper::formatCurrency($totalPayment) . '.',
          'icon'    => 'fa fa-shopping-cart ps-1',
          'link'    => route('account.orders.accounts', $code),
          'is_read' => false
      ]);

      // Check for Pre-order message
      $msg = 'Mua tài khoản mã số ' . $item->code . ' thành công';
      if ($resource->username === 'PREORDER_WAITING') {
          $msg = 'Đặt hàng trước thành công! Đơn hàng đang được xử lý.';
      }

      return response()->json([
        'data'    => [
          'is_bulk'        => false,
          'code'           => $code,
          'username'       => $resource->username,
          'discount'       => $item->discount,
          'original_price' => $item->price,
        ],
        'status'  => 200,
        'message' => $msg,
      ], 200);
    } else {
      //
      $code   = 'G2-' . Helper::randomString(8, true);
      $group  = isset($item->group) ? $item->group->name : '-';
      
      // Calculate discounted unit price
      $unitPrice = $totalPayment / $quantity;
      
      foreach ($resources as $resource) {
        // Skip PREORDER_WAITING placeholders - don't save them to database
        // They will be fulfilled by cron job and resources will be updated at that time
        if ($resource->username === 'PREORDER_WAITING') {
          continue;
        }
        
        ResourceV2O::create([
          'code'               => $resource->code,
          'type'               => $resource->type,
          'domain'             => Helper::getDomain(),
          'is_bulk'            => true,
          'username'           => $resource->username,
          'buyer_code'         => $code,
          'buyer_name'         => $user->username,
          'buyer_paym'         => $unitPrice,
          'buyer_date'         => now(),
          'buyer_ip'           => $request->ip(),
          'buyer_ua'           => $request->userAgent(),
          'warranty_expire_at' => $warrantyExpireAt,
          'order_status'       => 'Completed',
        ]);

        // Delete from inventory only if it's a real model (not a placeholder)
        if ($resource instanceof \App\Models\ResourceV2S) {
          $resource->delete();
        }
      }
      //
      $order = BulkOrder::create([
        'name'     => $group,
        'code'     => $code,
        'image'    => $item->group->image ?? null,
        'domain'   => Helper::getDomain(),
        'payment'  => $totalPayment,
        'user_id'  => $user->id,
        'username' => $user->username,
        'buyer_ip' => $request->ip(),
        'buyer_ua' => $request->userAgent(),
      ]);

      $user->transactions()->create([
        'code'           => $code,
        'amount'         => $totalPayment,
        'cost_amount'    => $item->cost,
        'balance_after'  => $user->balance,
        'balance_before' => $user->balance + $totalPayment,
        'domain'         => Helper::getDomain(),
        'type'           => 'account-v2-buy',
        'extras'         => [
          'code'       => $item->code,
          'group_id'   => $item->group_id,
          'account_id' => $item->id,
          'coupon_code'=> $coupon ? $coupon->coupon_code : null,
          'discount'   => $discountAmount,
          'api_product_id' => $targetApiId ?? null
        ],
        'status'         => 'paid',
        'content'        => 'Mua ' . $quantity . ' tài khoản trong nhóm ' . $group . ($coupon ? ' (Giảm giá: ' . Helper::formatCurrency($discountAmount) . ')' : ''),
        'user_id'        => $user->id,
        'username'       => $user->username,
      ]);

      $ref = $user->referrer;
      if ($ref !== null) {
        $affiliate = $ref->affiliate;
        if ($affiliate !== null) {
          $affiliate->increment('total_account_buy', $quantity);
        }
      }

      Helper::updateCommission($user->id, $totalPayment, 'order', $code);

      Helper::updateCommission($user->id, $totalPayment, 'order', $code);

      // Notification
      \App\Models\Notification::create([
          'user_id' => $user->id,
          'type'    => 'order',
          'title'   => 'Mua tài khoản thành công',
          'content' => 'Bạn đã mua thành công ' . $quantity . ' tài khoản (Mã đơn: ' . $code . ') với giá ' . \Helper::formatCurrency($totalPayment) . '.',
          'icon'    => 'fa fa-shopping-cart ps-1',
          'link'    => route('account.orders.accounts', $code), // Note: might need tab=bulk
          'is_read' => false
      ]);

      return response()->json([
        'data'    => [
          'code'           => $code,
          'group'          => $group,
          'is_bulk'        => true,
          'quantity'       => $quantity,
          'original_price' => $item->price,
        ],
        'status'  => 200,
        'message' => 'Chúc mừng bạn đã mua thành công ' . $resources->count() . ' tài khoản',
      ], 200);
    }
  }
}
