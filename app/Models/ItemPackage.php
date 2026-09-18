<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPackage extends Model
{
  use HasFactory;

  protected $fillable = [
    'group_id',
    'name',
    'image',
    'status',
    'priority',
  ];

  protected $casts = [
    'status'   => 'boolean',
    'group_id' => 'integer',
    'priority' => 'integer',
  ];

  public function group()
  {
    return $this->belongsTo(ItemGroup::class, 'group_id', 'id');
  }

  public function data()
  {
    return $this->hasMany(ItemData::class, 'package_id', 'id');
  }
}
