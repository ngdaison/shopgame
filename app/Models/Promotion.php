<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'min_deposit',
        'max_deposit',
        'bonus_value',
        'bonus_type',
        'payment_methods',
        'status',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'status' => 'boolean',
        'min_deposit' => 'integer',
        'max_deposit' => 'integer',
        'bonus_value' => 'integer',
    ];

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';

    const PAYMENT_METHODS = [
        'banking' => 'Ngân hàng',
        'card' => 'Thẻ cào',
        'usdt' => 'USDT',
        'paypal' => 'PayPal',
        'perfect_money' => 'Perfect Money',
    ];
}
