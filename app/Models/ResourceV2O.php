<?php

namespace App\Models;

use App\Traits\HasDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceV2O extends Model
{
  use HasFactory, HasDomain;

  protected $table = 'resource_v2_o';

  protected $fillable = [
    'code',
    'type',
    'domain',
    'is_bulk',
    'group_id',
    'username',
    'buyer_name',
    'buyer_code',
    'buyer_paym',
    'buyer_date',
    'order_status',
    'warranty_expire_at',
    'warranty_hours',
    'order_note',
    'buyer_ip',
    'buyer_ua',
  ];

  protected $hidden = [
    'username',
    'buyer_name',
    'buyer_code',
    'buyer_paym',
    'buyer_date',
  ];

  protected $casts = [
    'buyer_date' => 'datetime',
    'warranty_expire_at' => 'datetime',
  ];

  protected $appends = [
    'data',
    'product_name',
  ];

  public function getDataAttribute()
  {
    return $this->username;
  }

  public function getProductNameAttribute()
  {
    return $this->parent ? $this->parent->name : $this->code;
  }

  public function parent()
  {
    return $this->belongsTo(ListItemV2::class , 'code', 'code');
  }

  public function user()
  {
    return $this->belongsTo(User::class , 'buyer_name', 'username');
  }
}
