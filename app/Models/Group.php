<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'sub_name',
    'image',
    'type',
    'slug',
    'descr',
    'sold',
    'price',
    'priority',
    'meta_seo',
    'descr_seo',
    'discount',
    'currency',
    'status',
    'username',
    'game_type',
    'category_id',
    'category_name',
    'warranty_hours',
  ];

  protected $casts = [
    'status'      => 'boolean',
    'meta_seo'    => 'array',
    'category_id' => 'integer',
  ];

  protected $appends = [
    'revenue',
    'in_stock',
    'total_item',
    'sold_count',
  ];

  public function getRevenueAttribute()
  {
    return $this->items()->where('buyer_name', '!=', null)->where('buyer_code', '!=', null)->sum('buyer_paym') ?? 0;
  }

  public function getSoldCountAttribute()
  {
    return $this->items()->where('buyer_name', '!=', null)->where('buyer_code', '!=', null)->count();
  }

  public function getTotalItemAttribute()
  {
    return $this->items()->count();
  }

  public function getInStockAttribute()
  {
    return $this->items()->where('buyer_name', null)->where('buyer_code', null)->count();
  }

  public static function generateSlug($name)
  {
    $slug = str()->slug($name);

    if (self::where('slug', $slug)->exists()) {
      $slug .= '-' . rand(1000, 9999);
    }

    return $slug;
  }

  public function categories()
  {
    return $this->morphToMany(Category::class, 'categoryable');
  }

  public function items()
  {
    return $this->hasMany(ListItem::class);
  }
}
