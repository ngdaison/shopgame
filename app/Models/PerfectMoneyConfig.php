<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfectMoneyConfig extends Model
{
    use HasFactory;

    protected $table = 'perfect_money_config';

    protected $fillable = [
        'config',
        'perfect_money_accounts',
    ];

    protected $casts = [
        'config' => 'array',
        'perfect_money_accounts' => 'array',
    ];
}
