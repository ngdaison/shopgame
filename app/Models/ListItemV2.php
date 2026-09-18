<?php

namespace App\Models;

use App\Models\ResourceV2O;
use Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListItemV2 extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'type',
    'code',
    'cost',
    'image',
    'price',
    'status',
    'is_bulk',
    'discount',
    'priority',
    'group_id',
    'list_image',
    'highlights',
    'description',
    'warranty_hours',
    'resource_code',
    'client_type',
    'api_domain',
    'api_key',
    'api_config_id',
    'api_id',
    'api_coupon',
    'allow_preorder',
  ];

  protected $hidden = [
    'cost',
    'profit',
    'revenue',
    'resources',
    'extra_data',
    'resource_code',
  ];

  protected $casts = [
    'status'     => 'boolean',
    'is_bulk'    => 'integer',
    'list_image' => 'array',
    'highlights' => 'array',
  ];

  protected $appends = [
    'profit',
    'sold',
    'amount',
    'revenue',
    'payment',
    'price_str',
    'price_discount',
    'original_price_str',
  ];

  public function getIsBulkAttribute($value)
  {
    // tự ý thay đổi giá trị này license sẽ bị hủy / ngưng hỗ trợ!
    // if (!feature_enabled('bulk-orders')) {
    //   return 1;
    // }

    return (int) $value;
  }

  public function getProfitAttribute()
  {
    // $cost    = $this->resources->where('buyer_name', '!=', null)->count() * $this->cost;
    // $payment = $this->resources->where('buyer_name', '!=', null)->sum('buyer_paym');

    // return ($payment - $cost);

    return -1;
  }

  public function getAmountAttribute()
  {
    if ($this->client_type === 'api') {
        $apiDomain = $this->api_domain;
        $apiKey = $this->api_key;
        $apiInfo = $this->api_id;

        if ($this->api_config_id && $this->apiConfig) {
            $apiDomain = $this->apiConfig->url;
            $apiKey = $this->apiConfig->api_key;
        }

        if ($apiDomain && $apiKey && $apiInfo) {
            $apiIds = explode(',', $apiInfo);
            $totalStock = 0;

            foreach ($apiIds as $apiId) {
                $apiId = trim($apiId);
                if (empty($apiId)) continue;
                
                $cacheKey = 'api_product_' . $apiId . '_' . md5($apiDomain);
                
                $stock = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($apiDomain, $apiKey, $apiId) {
                    try {
                        // Use singular product endpoint for better reliability
                        $response = \Illuminate\Support\Facades\Http::get(rtrim($apiDomain, '/') . '/api/product.php', [
                            'api_key' => $apiKey,
                            'product' => $apiId
                        ]);
                        
                        if ($response->successful()) {
                            $json = $response->json();
                            $data = $json['product'] ?? $json['data'] ?? $json['products'] ?? $json;
                            
                            if (is_array($data)) {
                                if (isset($data[0])) {
                                    $item = $data[0];
                                    return (int) ($item['amount'] ?? $item['quantity'] ?? $item['in_stock'] ?? 0);
                                }
                                return (int) ($data['amount'] ?? $data['quantity'] ?? $data['in_stock'] ?? 0);
                            }
                        }
                        return 0;
                    } catch (\Exception $e) {
                        return 0;
                    }
                });

                // If API returns 0, try to get from products_data JSON in apiConfig
                if ($stock <= 0 && $this->api_config_id && $this->apiConfig) {
                    $productsData = $this->apiConfig->products_data ?? [];
                    if (is_array($productsData)) {
                        foreach ($productsData as $category) {
                            if (is_array($category) && isset($category['products'])) {
                                foreach ($category['products'] as $product) {
                                    if (($product['id'] ?? null) == $apiId || ($product['slug'] ?? null) == $apiId) {
                                        $stock = (int) ($product['amount'] ?? $product['quantity'] ?? $product['in_stock'] ?? 0);
                                        if ($stock > 0) break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                
                $totalStock += $stock;
            }

            return $totalStock;
        }
    }

    return $this->resources->where('buyer_name', null)->where('buyer_code', null)->count();
  }

  public function getSoldAttribute()
  {
    return ResourceV2O::where('code', $this->code)->count();
  }

  public function getPaymentAttribute()
  {
    $payment = $this->price;

    if ($this->discount > 0) {
      $payment = $this->price - ($this->price * $this->discount / 100);
    }

    return $payment;
  }

  public function getPriceStrAttribute()
  {
    $totalPrice = $this->price;
    if ($this->discount > 0) {
      $totalPrice = $this->price - ($this->price * $this->discount / 100);
    }

    return Helper::formatCurrency($totalPrice);
  }

  public function getPriceDiscountAttribute()
  {
    if ($this->discount === 0) {
      return 0;
    }

    $discountedPrice = $this->price - ($this->price * $this->discount / 100);

    return Helper::formatCurrency($discountedPrice);
  }

  public function getOriginalPriceStrAttribute()
  {
    return Helper::formatCurrency($this->price);
  }

  public static function generateCode()
  {
    // Generate a random 10-digit code
    $code = Helper::randomNumber(10);

    // Ensure code doesn't already exist
    if (self::where('code', $code)->exists()) {
      return self::generateCode();
    }

    return $code;
  }

  public function getRevenueAttribute()
  {
    $revenue = ResourceV2O::where('code', $this->code)->sum('buyer_paym');

    return ($revenue);
  }

  public function group()
  {
    return $this->belongsTo(GroupV2::class);
  }

  public function resources()
  {
    return $this->hasMany(ResourceV2S::class, 'code', 'code');
  }

  public function apiConfig()
  {
      return $this->belongsTo(AccountV2Api::class, 'api_config_id');
  }
    public function getAvailableApiProduct()
    {
        if ($this->client_type !== 'api' || empty($this->api_id)) {
            return null;
        }

        $apiIds = explode(',', $this->api_id);
        shuffle($apiIds); // Load balancing

        $apiDomain = $this->api_domain;
        $apiKey = $this->api_key;
        
        if ($this->api_config_id && $this->apiConfig) {
            $apiDomain = $this->apiConfig->url;
            $apiKey = $this->apiConfig->api_key;
        }

        if (!$apiDomain || !$apiKey) {
            return null;
        }

        foreach ($apiIds as $id) {
            $id = trim($id);
            if (empty($id)) continue;
            
            $cacheKey = 'api_product_' . $id . '_' . md5($apiDomain);
            $stock = \Illuminate\Support\Facades\Cache::get($cacheKey);
            
            if ($stock > 0) {
                return $id;
            }
        }
        
        // Fallback: Return first one
        return trim($apiIds[0] ?? '');
    }
}
