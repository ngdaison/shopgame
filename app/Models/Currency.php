<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'rate',
        'decimals',
        'symbol_left',
        'symbol_right',
        'separator',
        'status',
        'is_default',
        'rate_mode',
        'last_synced_at',
        'domain',
    ];

    protected $casts = [
        'rate' => 'decimal:12', // Ensure high precision
        'decimals' => 'integer',
        'status' => 'boolean',
        'is_default' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
