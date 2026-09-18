<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityBan extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'username',
        'ip',
        'reason',
        'strikes',
        'status',
        'banned_until',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'banned_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
