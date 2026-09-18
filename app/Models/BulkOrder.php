<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkOrder extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'code',
    'type',
    'group',
    'image',
    'domain',
    'payment',
    'user_id',
    'username',
    'order_note',
  ];

  public function orders()
  {
    return $this->hasMany(ResourceV2O::class, 'buyer_code', 'code');
  }
}
