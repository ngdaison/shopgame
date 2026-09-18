<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LanguageDomainSetting extends Model
{
    use HasFactory;

    protected $table = 'language_domain_settings';

    protected $fillable = [
        'language_id',
        'domain_id',
        'config_json',
    ];

    protected $casts = [
        'config_json' => 'array',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function domain_setting()
    {
        return $this->belongsTo(DomainSetting::class, 'domain_id');
    }
}
