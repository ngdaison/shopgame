<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'iso_code',
        'domain',
        'status',
        'is_default',
        'translations',
        'domain_settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean',
        'translations' => 'array',
        'domain_settings' => 'array',
    ];
}
