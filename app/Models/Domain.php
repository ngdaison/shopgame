<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'host',
        'type',
        'target',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];
}
