<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PinGroup extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'link',
    'image',
    'status',
    'open_type',
  ];

  protected $casts = [
    'status' => 'boolean',
  ];
}
