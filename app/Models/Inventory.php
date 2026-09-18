<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'value',
    'var_id',
    'user_id',
    'username',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  public function inventory_var()
  {
    return $this->belongsTo(InventoryVar::class, 'var_id', 'id');
  }

}
