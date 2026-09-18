<?php

namespace App\Models;

use App\Traits\HasDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Transaction extends Model
{
  use HasFactory, HasDomain;

  const STATUS_PROCESSING = 'processing';
  const STATUS_COMPLETED = 'completed';
  const STATUS_CANCELLED = 'cancelled';
  const STATUS_PENDING = 'pending';

  protected $fillable = [
    'code',
    'amount',
    'cost_amount',
    'balance_after',
    'balance_before',
    'type',
    'extras',
    'order_id',
    'sys_note',
    'status',
    'content',
    'user_id',
    'username',
    'domain',
    'warranty_expire_at',
  ];

  protected $hidden = [
    'order_id',
    'sys_note',
    'extras',
  ];

  protected $appends = [
    'prefix',
    'domain_display',
  ];

  protected $casts = [
    'extras' => 'array',
    'order_id' => 'string',
  ];

  public function getPrefixAttribute()
  {
    return $this->balance_before > $this->balance_after ? '-' : '+';
  }

  public function getIsPaidAttribute()
  {
    $status = strtolower($this->status);
    return $status === self::STATUS_COMPLETED || $status === 'success';
  }

  public function user()
  {
    return $this->belongsTo(User::class , 'user_id');
  }

}
