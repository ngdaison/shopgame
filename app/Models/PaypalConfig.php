<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaypalConfig extends Model
{
    use HasFactory;

    protected $table = 'paypal_config';

    protected $fillable = [
        'config',
        'paypal_accounts',
    ];

    protected $casts = [
        'config' => 'array',
        'paypal_accounts' => 'array',
    ];
}
