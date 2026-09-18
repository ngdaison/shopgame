<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
  use HasFactory;

  protected $fillable = [
    'code',
    'user_id',
    'clicks',
    'signups',
    'username',
    'purchases',
    'commissions',
    'total_deposit',
    'total_item_buy',
    'total_register',
    'total_boost_buy',
    'total_account_buy',
  ];


}
