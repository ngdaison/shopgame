<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingames extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'content',
    'status',
  ];

  protected $casts = [
    'content' => 'array',
    'status'  => 'boolean',
  ];

}
