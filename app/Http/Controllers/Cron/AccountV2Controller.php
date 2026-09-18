<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Models\ResourceV2O;
use App\Models\ResourceV2S;
use App\Models\User;
use App\Models\Notification;
use App\Models\ListItemV2;
use App\Models\AccountV2Api;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AccountV2Controller extends Controller
{
  public function handle(Request $request)
  {
    // Fetch pending pre-orders - only those with PREORDER_WAITING status
    $pendingOrders = ResourceV2O::where('order_status', 'Processing')
                                ->where('username', 'PREORDER_WAITING')
                                ->orderBy('created_at', 'asc')
                                ->get();
    
    $fulfilledCount = 0;
    $details = [];

    foreach ($pendingOrders as $order) {
      // Double-check status to prevent processing same order twice
      if ($order->order_status !== 'Processing' || $order->username !== 'PREORDER_WAITING') {
        continue;
      }

      $item = ListItemV2::where('code', $order->code)->first();
      if (!$item) {
        // Mark as failed to avoid processing again
        $order->update(['order_status' => 'Failed']);
        $details[] = [
          'ma_don_hang' => $order->buyer_code,
          'trang_thai' => 'loi',
          'ghi_chu' => 'Sản phẩm không tồn tại (Code: ' . $order->code . ')'
        ];
        continue;
      }

      $itemStatus = [
        'Mã đơn hàng' => $order->buyer_code,
        'Sản phẩm' => $item->name,
        'Loại' => ($item->client_type === 'api' ? 'Kết nối API' : 'Kho hệ thống'),
      ];

      if ($item->client_type === 'api') {
        // API Fulfillment
        try {
          // Check stock first using the logic from ListItemV2
          $amount = $item->amount; // This triggers getAmountAttribute()
          $itemStatus['Tồn kho API'] = $amount;

          if ($amount <= 0) {
            // API không có hàng - giữ nguyên trạng thái Processing, không xử lý
            $itemStatus['Trạng thái'] = 'Chờ xử lý';
            $itemStatus['Ghi chú'] = 'API Đại lý hết hàng';
            $details[] = $itemStatus;
            continue;
          }

          // Get API configuration - support both api_config_id and direct api_domain
          $apiDomain = $item->api_domain;
          $apiKey = $item->api_key;
          $apiCoupon = $item->api_coupon;
          
          if ($item->api_config_id && $item->apiConfig) {
              $apiDomain = $item->apiConfig->url;
              $apiKey = $item->apiConfig->api_key;
              if (empty($apiCoupon)) {
                  $apiCoupon = $item->apiConfig->coupon;
              }
          }

          if (!$apiDomain || !$apiKey || !$item->api_id) {
              $itemStatus['Trạng thái'] = 'Lỗi';
              $itemStatus['Ghi chú'] = 'Cấu hình API không hợp lệ';
              $details[] = $itemStatus;
              continue;
          }

          $apiPayload = [
            'action' => 'buyProduct',
            'id' => $item->api_id,
            'amount' => 1,
            'api_key' => $apiKey,
          ];
          
          if ($apiCoupon) {
            $apiPayload['coupon'] = $apiCoupon;
          }
          
          // Call API to buy product
          $baseUrl = rtrim($apiDomain, '/');
          $endpoint = $baseUrl . '/api/buy_product';
          
          $response = Http::asForm()->post($endpoint, $apiPayload);
          
          // If first try fails, try with .php
          if ($response->failed()) {
              $response = Http::asForm()->post($endpoint . '.php', $apiPayload);
          }
          
          $apiResult = $response->json();
          
          // Check for success - support various response formats
          $isSuccess = false;
          $productData = null;
          
          if (isset($apiResult['status'])) {
              if ($apiResult['status'] === 'success' || $apiResult['status'] === true) {
                  $isSuccess = true;
              }
          }
          
          // Get product data - support various field names
          if ($isSuccess && isset($apiResult['data'])) {
              $productData = $apiResult['data'];
          } elseif ($isSuccess && isset($apiResult['product'])) {
              $productData = $apiResult['product'];
          }
          
          if ($isSuccess && !empty($productData)) {
              // Handle both array and single item
              $line = null;
              if (is_array($productData)) {
                  $line = trim(strip_tags($productData[0] ?? ''));
              } else {
                  $line = trim(strip_tags($productData));
              }
              
              if (!empty($line)) {
                  $order->update([
                    'username'     => $line,
                    'order_status' => 'Completed',
                  ]);

                  $this->notifyUser($order);
                  $fulfilledCount++;
                  $itemStatus['Trạng thái'] = 'Thành công';
                  $itemStatus['Ghi chú'] = 'Duyệt thành công từ API';
              } else {
                  $itemStatus['Trạng thái'] = 'Lỗi';
                  $itemStatus['Ghi chú'] = 'API trả về dữ liệu không hợp lệ';
              }
          } else {
              $itemStatus['Trạng thái'] = 'Lỗi';
              $itemStatus['Ghi chú'] = $apiResult['msg'] ?? $apiResult['message'] ?? $apiResult['error'] ?? json_encode($apiResult);
          }
        } catch (\Exception $e) {
            $itemStatus['Trạng thái'] = 'Lỗi';
            $itemStatus['Ghi chú'] = 'Lỗi gọi API: ' . $e->getMessage();
        }
      } else {
        // Normal Fulfillment (Local Stock)
        $account = ResourceV2S::where('code', $order->code)
                              ->where(function($q) {
                                  $q->whereNull('buyer_name')->orWhere('buyer_name', '');
                              })
                              ->where(function($q) {
                                  $q->whereNull('buyer_code')->orWhere('buyer_code', '');
                              })
                              ->first();

        if ($account) {
          $order->update([
            'username'     => $account->username,
            'order_status' => 'Completed',
          ]);

          $account->delete();

          $this->notifyUser($order);
          $fulfilledCount++;
          $itemStatus['Trạng thái'] = 'Thành công';
          $itemStatus['Ghi chú'] = 'Duyệt thành công từ kho hệ thống';
        } else {
          $itemStatus['Trạng thái'] = 'Chờ xử lý';
          $itemStatus['Ghi chú'] = 'Kho hệ thống hết hàng';
        }
      }
      $details[] = $itemStatus;
    }

    $totalRemaining = ResourceV2O::where('order_status', 'Processing')
                                ->where('username', 'PREORDER_WAITING')
                                ->count();

    // --- Background Stock Refresh for Performance ---
    $apiItems = ListItemV2::where('client_type', 'api')->get();
    $refreshResults = [];

    foreach ($apiItems as $apiItem) {
        // Determine API credentials
        $apiDomain = $apiItem->api_domain;
        $apiKey = $apiItem->api_key;

        if ($apiItem->api_config_id && $apiItem->apiConfig) {
            $apiDomain = $apiItem->apiConfig->url;
            $apiKey = $apiItem->apiConfig->api_key;
        }

        // Validate basic requirements
        if (!$apiDomain || !$apiKey || !$apiItem->api_id) {
            continue;
        }

        // Support multiple API IDs (comma separated)
        $apiIds = explode(',', $apiItem->api_id);

        foreach ($apiIds as $apiId) {
            $apiId = trim($apiId);
            if (empty($apiId)) continue;

            $cacheKey = 'api_product_' . $apiId . '_' . md5($apiDomain);

            try {
                // Use singular product endpoint matching ListItemV2 logic
                $response = Http::get(rtrim($apiDomain, '/') . '/api/product.php', [
                    'api_key' => $apiKey,
                    'product' => $apiId
                ]);
            
                if ($response->successful()) {
                    $json = $response->json();
                    $data = $json['product'] ?? $json['data'] ?? $json['products'] ?? $json;
                    $amount = 0;
                    
                    if (is_array($data)) {
                        if (isset($data[0])) {
                             // Handle array of products (though we asked for one)
                            $itemData = $data[0];
                            $amount = (int) ($itemData['amount'] ?? $itemData['quantity'] ?? $itemData['in_stock'] ?? 0);
                        } else {
                            // Handle single object
                            $amount = (int) ($data['amount'] ?? $data['quantity'] ?? $data['in_stock'] ?? 0);
                        }
                    }

                    // Cache for 1 hour
                    \Illuminate\Support\Facades\Cache::put($cacheKey, $amount, 3600);
                    
                    $refreshResults[] = [
                        'Sản phẩm' => $apiItem->name . " (ID: $apiId)",
                        'Số lượng' => $amount,
                        'Trạng thái' => 'Đã cập nhật'
                    ];
                }
            } catch (\Exception $e) {
                $refreshResults[] = [
                    'Sản phẩm' => $apiItem->name . " (ID: $apiId)",
                    'Trạng thái' => 'Lỗi: ' . $e->getMessage()
                ];
            }
        }
    }

    // --- NEW: Global Product Sync for Pre-loading ---
    $apis = AccountV2Api::all();
    $totalSynced = 0;
    foreach ($apis as $api) {
        try {
            $syncResp = Http::get(rtrim($api->url, '/') . '/api/products.php', [
                'api_key' => $api->api_key,
            ]);

            if ($syncResp->successful()) {
                $syncData = $syncResp->json();
                if (isset($syncData['status']) && $syncData['status'] && isset($syncData['categories'])) {
                    // Cache products data in account_v2_apis instead of separate table
                    $api->setProducts($syncData['categories'])->save();
                    // Count total products for reporting
                    foreach ($syncData['categories'] as $category) {
                        $totalSynced += count($category['products'] ?? []);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Global Sync error in Cron: ' . $e->getMessage());
        }
    }

    return response()->json([
      'Trạng thái'  => 200,
      'Thông báo' => 'Đã duyệt hoàn tất ' . $fulfilledCount . ' đơn hàng và đồng bộ ' . $totalSynced . ' sản phẩm',
      'Số lượng đã xử lý' => count($details),
      'Cập nhật kho API' => $refreshResults,
      'Đồng bộ sản phẩm' => $totalSynced
    ]);
  }

  private function notifyUser($order)
  {
    if ($order->buyer_name) {
      $user = User::where('username', $order->buyer_name)->first();
      if ($user) {
        Notification::create([
          'user_id' => $user->id,
          'type'    => 'order',
          'title'   => 'Đơn đặt trước đã được xử lý',
          'content' => 'Đơn hàng đặt trước của bạn (Mã: ' . $order->buyer_code . ') đã được xử lý thành công. Bạn có thể xem thông tin tài khoản ngay bây giờ.',
          'icon'    => 'fa fa-check-circle ps-1',
          'link'    => route('account.orders.accounts', $order->buyer_code),
          'is_read' => false
        ]);
      }
    }
  }
}
