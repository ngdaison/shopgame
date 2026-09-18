<?php

namespace App\Models;

use Helper;
use App\Traits\HasDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
  use HasFactory, HasDomain;

  const STATUS_PENDING = 'pending';
  const STATUS_COMPLETED = 'completed';
  const STATUS_PAID = 'paid';
  const STATUS_EXPIRED = 'expired';
  const STATUS_CANCELLED = 'cancelled';

  protected $fillable = [
    'code',
    'type',
    'status',
    'amount',
    'user_id',
    'username',
    'currency',
    'trans_id',
    'order_id',
    'request_id',
    'description',
    'payment_details',
    'paid_at',
    'paid_at',
    'expired_at',
    'domain',
  ];

  protected $hidden = [
    'trans_id',
    'order_id',
    'request_id',
    'payment_details',
  ];

  protected $casts = [
    'paid_at' => 'datetime',
    'expired_at' => 'datetime',
    'payment_details' => 'array',
  ];

  protected $appends = [
    'is_paid',
    'is_expired',
    'expired_str',
    'status_class',
    'domain_display',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public static function generateCode()
  {
    $code = 'SNV3-' . rand(100000, 999999);
    if (self::where('code', $code)->exists()) {
      return self::generateCode();
    }

    return $code;
  }

  public function getIsPaidAttribute()
  {
    $status = strtolower($this->status);
    return $status === self::STATUS_COMPLETED || $status === self::STATUS_PAID;
  }

  public function getIsExpiredAttribute()
  {
    if ($this->expired_at === null) {
      return true;
    }

    if ($this->getIsPaidAttribute()) {
      return true;
    }

    if ($this->status === 'Expired') {
      return true;
    }

    if ($this->status === 'Cancelled') {
      return true;
    }

    $isPast = $this->expired_at->isPast();

    if ($isPast && $this->status === 'Pending') {
      $this->update(['status' => 'Expired']);
    }

    return $isPast;
  }

  public function getExpiredStrAttribute()
  {
    if ($this->expired_at === null) {
      return null;
    }

    return Helper::getRemainingHours($this->expired_at, '%h giờ');
  }

  public function getStatusClassAttribute()
  {
    $status = strtolower($this->status);
    if ($status === self::STATUS_PAID || $status === self::STATUS_COMPLETED) {
      return 'success';
    }

    if ($status === self::STATUS_PENDING || $status === 'processing') {
      return 'warning';
    }

    if ($status === self::STATUS_EXPIRED) {
      return 'danger';
    }

    if ($status === self::STATUS_CANCELLED) {
      return 'error';
    }

    return 'error';
  }
}
