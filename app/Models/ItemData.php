<?php

namespace App\Models;

use Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemData extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'type',
    'code',
    'image',
    'price',
    'robux',
    'discount',
    'status',
    'sold_count',
    'highlights',
    'currency',
    'ingame_id',
    'description',
    'extra_data',
    'priority',
    'group_id',
    'package_id',
    'warranty_hours',
  ];

  protected $casts = [
    'status'         => 'boolean',
    'highlights'     => 'array',
    'sold_count'     => 'integer',
    'warranty_hours' => 'integer',
  ];

  protected $appends = [
    'payment',
    'price_str',
    'payment_str',
    'ingame_list', // 'ingames' => 'ingames_id',
    'price_discount',
  ];

  public function getPaymentAttribute()
  {
    if (is_numeric($this->robux) && $this->robux > 0) {
      $rate = Helper::getRobuxRate($this->robux, setting('rate_robux', 100));
      $payment = $this->robux * $rate;
    } else {
      $payment = $this->price;
    }

    if ($this->discount > 0) {
      $payment = $this->price - ($this->price * $this->discount / 100);
    }

    return $payment;
  }

  public static function generateCode()
  {
    $code = date('y') . date('m') . Helper::randomNumber(4);

    if (self::where('code', $code)->exists()) {
      return self::generateCode();
    }

    return $code;
  }

  public function getPriceStrAttribute()
  {
    $totalPrice = $this->price;

    if ($this->discount > 0) {
      $totalPrice = $this->price - ($this->price * $this->discount / 100);
    }

    return Helper::formatCurrency($totalPrice);
  }

  public function getPaymentStrAttribute()
  {
    $payment = $this->payment;

    return Helper::formatCurrency($payment);
  }

  public function getIngameListAttribute()
  {
    $data = $this->ingames()->first();
    if ($data !== null) {
      return $data->content;
    } else {
      return [];
    }
  }

  public function getPriceDiscountAttribute()
  {
    if ($this->discount === 0) {
      return 0;
    }

    $discount = $this->price - ($this->price * $this->discount / 100);

    return Helper::formatCurrency($discount);
  }

  public function group()
  {
    return $this->belongsTo(ItemGroup::class, 'group_id', 'id');
  }

  public function package()
  {
    return $this->belongsTo(ItemPackage::class, 'package_id', 'id');
  }



  public function packages()
  {
    return $this->belongsToMany(ItemPackage::class, 'item_data_package', 'item_data_id', 'item_package_id');
  }

  public function ingames()
  {
    return $this->belongsTo(Ingames::class, 'ingame_id', 'id');
  }
}
