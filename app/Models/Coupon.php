<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_code',
        'product_ids',
        'quantity',
        'user_usage_limit',
        'used',
        'discount_type',
        'discount_value',
        'min_order_value',
        'start_datetime',
        'end_datetime'
    ];

    protected $casts = [
        'product_ids' => 'array',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'discount_value' => 'float',
        'min_order_value' => 'float',
        'user_usage_limit' => 'integer',
    ];

    /**
     * Check if coupon is valid for use.
     */
    public function isValid()
    {
        $now = now();
        
        // Check dates
        if ($this->start_datetime && $now->lt($this->start_datetime)) {
            return false;
        }
        if ($this->end_datetime && $now->gt($this->end_datetime)) {
            return false;
        }

        // Check quantity
        if ($this->quantity > 0 && $this->used >= $this->quantity) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount.
     * 
     * @param float $total Order total
     * @return float
     */
    public function calculateDiscount($total)
    {
        $value = (float) $this->discount_value;
        $total = (float) $total;

        if ($this->discount_type === 'percentage') {
            return $total * ($value / 100);
        } else {
            return min($value, $total);
        }
    }

    /**
     * Check if coupon is applicable to current cart/products.
     *
     * @param float $cartTotal
     * @param array $cartProductIds Array of product uni_ids (e.g. ['account-1', 'item-5'])
     * @return bool
     */
    public function isApplicable($cartTotal, $cartProductIds = [])
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check Minimum Order Value
        if ($this->min_order_value && $cartTotal < $this->min_order_value) {
            return false;
        }

        // Check Product Applicability
        // If product_ids is null or empty, it applies to all.
        if (empty($this->product_ids)) {
            return true;
        }

        // If specific products are set, check if at least one cart product is in the list
        // logic: strict intersection or just containment? Usually if cart has at least one valid item.
        // Or does the coupon only apply to those specific items?
        // For simplicity, we check if the intersection of cartProducts and couponProducts is not empty.
        $intersection = array_intersect($cartProductIds, $this->product_ids);
        
        return count($intersection) > 0;
    }

    /**
     * Check if coupon is valid for specific user.
     * 
     * @param int $userId
     * @return bool
     */
    public function isValidForUser($userId)
    {
        if ($this->user_usage_limit > 0) {
            $usedCount = \App\Models\Transaction::where('user_id', $userId)
                ->whereIn('status', ['paid', 'completed', 'success'])
                ->where('extras->coupon_code', $this->coupon_code)
                ->count();
            
            if ($usedCount >= $this->user_usage_limit) {
                return false;
            }
        }
        return true;
    }
}
