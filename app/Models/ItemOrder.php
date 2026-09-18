<?php

namespace App\Models;

use App\Traits\HasDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOrder extends Model
{
  use HasFactory, HasDomain;

  protected $fillable = [
    'name',
    'type',
    'code',
    'data',
    'image',
    'payment',
    'discount',
    'robux',
    'rate_robux',
    'status',
    'input_user',
    'input_pass',
    'input_auth',
    'input_ingame',
    'input_contact',
    'input_ingame_n',
    'admin_note',
    'order_note',
    'extra_data',
    'warranty_expire_at',

    'assigned_to',
    'assigned_at',
    'assigned_note',
    'assigned_type',
    'assigned_status',
    'assigned_payment',
    'assigned_complain',
    'assigned_completed',

    'user_id',
    'username',
    'domain',
  ];

  protected $hidden = [
    'data',
    'admin_note',
    'input_pass',
    'extra_data',
  ];

  protected $casts = [
    'data' => 'array',
    'assigned_at' => 'datetime',
    'extra_data' => 'array',
    'input_ingame' => 'array',
    'assigned_payment' => 'double',
    'assigned_complain' => 'boolean',
    'assigned_completed' => 'datetime',
    'warranty_expire_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
