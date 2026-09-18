<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsdtConfig extends Model
{
    use HasFactory;

    protected $table = 'usdt_config';

    protected $fillable = [
        'config',
        'usdt_accounts',
    ];

    protected $casts = [
        'config' => 'array',
        'usdt_accounts' => 'array',
    ];
}
