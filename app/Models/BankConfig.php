<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankConfig extends Model
{
  use HasFactory;

  protected $table = 'bank_config';

  protected $fillable = [
    'config',
    'bank_accounts', // JSON
  ];

  protected $casts = [
    'config' => 'array',
    'bank_accounts' => 'array',
  ];

  // Helper to get bank accounts as collection if needed, though 'array' cast does mostly same
}
