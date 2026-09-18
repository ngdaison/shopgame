<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GBGroup extends Model
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
    'discount',
    'currency',
    'status',
    'username',
    'priority',
    'category_id',
    'category_name',
  ];

  protected $casts = [
    'status'      => 'boolean',
    'category_id' => 'integer',
  ];

  public function categories()
  {
    return $this->morphToMany(Category::class, 'categoryable');
  }

  public function packages()
  {
    return $this->hasMany(GBPackage::class, 'group_id', 'id');
  }

  public static function generateSlug($name)
  {
    $slug = str()->slug($name);

    if (self::where('slug', $slug)->exists()) {
      $slug .= '-' . rand(1000, 9999);
    }

    return $slug;
  }
}
