<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemGroup extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'sub_name',
    'image',
    'image_package',
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
    'login_with',
    'category_id',
    'category_name',
    'warranty_hours',
    'is_center',
  ];

  protected $casts = [
    'status'      => 'boolean',
    'login_with'  => 'array',
    'category_id' => 'integer',
    'is_center'   => 'boolean',
  ];

  public static function generateSlug($name)
  {
    $slug = str()->slug($name);

    if (self::where('slug', $slug)->exists()) {
      $slug .= '-' . rand(1000, 9999);
    }

    return $slug;
  }

  public function data()
  {
    return $this->hasMany(ItemData::class, 'group_id', 'id');
  }

  public function packages()
  {
    return $this->hasMany(ItemPackage::class, 'group_id', 'id')->orderBy('priority', 'desc');
  }

  public function categories()
  {
    return $this->morphToMany(Category::class, 'categoryable');
  }
}
