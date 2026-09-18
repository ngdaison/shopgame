<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceV2S extends Model
{
  use HasFactory;

  protected $table = 'resource_v2_s';

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
    'status',
    'order_status',
    'warranty_expire_at',
    'warranty_hours',
  ];

  protected $hidden = [
    'data',
  ];

  public function parent()
  {
    return $this->belongsTo(ListItemV2::class, 'code', 'code');
  }
}
