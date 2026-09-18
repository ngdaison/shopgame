<?php

namespace App\Models;

use Helper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GBPackage extends Model
{
  use HasFactory;

  protected $fillable = [
    'code',
    'name',
    'image',
    'input',
    'price',
    'descr',
    'warranty_hours',
    'status',
    'priority',
    'group_id',
    'sold_count',
  ];

  protected $casts = [
    'status'   => 'boolean',
    'group_id' => 'integer',
    'priority' => 'integer',
  ];

  public function group()
  {
    return $this->belongsTo(GBGroup::class);
  }

  public function products()
  {
    return $this->hasMany(GBProduct::class, 'package_id');
  }

  public static function generateCode()
  {
    $code = date('y') . date('m') . Helper::randomNumber(4);

    if (self::where('code', $code)->exists()) {
      return self::generateCode();
    }

    return $code;
  }

}
