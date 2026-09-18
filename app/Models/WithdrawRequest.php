<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
  use HasFactory;

  protected $fillable = [
    'code',
    'user_id',
    'username',
    'var_id',
    'unit',
    'name',
    'amount',
    'user_inputs',
    'status',
    'after_value',
    'user_note',
    'admin_note',
  ];

  protected $casts = [
    'user_inputs' => 'array',
    'amount'      => 'integer',
  ];

  public function inventoryVar()
  {
    return $this->belongsTo(InventoryVar::class, 'var_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
