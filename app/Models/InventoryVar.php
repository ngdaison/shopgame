<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryVar extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'unit',
    'image',
    'is_active',
    'form_inputs',
    'form_packages',
    'min_withdraw',
    'max_withdraw',
    'descr',

  ];

  protected $casts = [
    'is_active'     => 'boolean',
    'form_inputs'   => 'array',
    'form_packages' => 'array',
    'min_withdraw'  => 'integer',
    'max_withdraw'  => 'integer',
  ];

  public function inventories()
  {
    return $this->hasMany(Inventory::class, 'var_id', 'id');
  }
}
