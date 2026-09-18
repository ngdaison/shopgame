<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceV2 extends Model
{
  use HasFactory;

  protected $fillable = [
    'code',
    'type',
    'domain',
    'is_bulk',
    'username',
    'buyer_name',
    'buyer_code',
    'buyer_paym',
    'buyer_date',
    'order_status',
    'warranty_expire_at',
    'warranty_hours',
    'order_note',
  ];

  protected $hidden = [
    'username',
    'buyer_name',
    'buyer_code',
    'buyer_paym',
    'buyer_date',
  ];

  public function parent()
  {
    return $this->belongsTo(ListItemV2::class, 'code', 'code');
  }

  protected $casts = [
    'buyer_date' => 'datetime',
    'warranty_expire_at' => 'datetime',
  ];

}
