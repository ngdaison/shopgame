<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountV2Api extends Model
{
    use HasFactory;

    protected $table = 'account_v2_apis';

    protected $fillable = [
        'name',
        'type',
        'category',
        'url',
        'api_key',
        'coupon',
        'products_data',
    ];

    protected $casts = [
        'products_data' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(ListItemV2::class, 'api_config_id');
    }

    /**
     * Get all products for this API
     * Returns cached products_data
     */
    public function getProducts()
    {
        return $this->products_data ?? [];
    }

    /**
     * Update products data (cached from external API)
     */
    public function setProducts($products)
    {
        $this->products_data = $products;
        return $this;
    }
}
