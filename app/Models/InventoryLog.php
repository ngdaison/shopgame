<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
  use HasFactory;

  protected $fillable = [
    'unit',
    'unit_id',

    'type',
    'value',
    'content',

    'after_value',
    'before_value',

    'user_id',
    'username',

    'source',
    'source_id',
    'status',
  ];

  protected $casts = [
    'value'        => 'integer',
    'after_value'  => 'integer',
    'before_value' => 'integer',
  ];

  public function inventoryVar()
  {
    return $this->belongsTo(InventoryVar::class, 'unit_id');
  }
}
