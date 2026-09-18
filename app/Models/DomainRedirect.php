<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DomainRedirect extends Model
{
    protected $fillable = [
        'source_domain_id',
        'target_domain_id',
        'status',
    ];

    public function source()
    {
        return $this->belongsTo(DomainSetting::class, 'source_domain_id');
    }

    public function target()
    {
        return $this->belongsTo(DomainSetting::class, 'target_domain_id');
    }
}
