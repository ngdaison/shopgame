<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'updated_at'
    ];

    public $timestamps = false; 

    protected $casts = [
        'setting_value' => 'array',
        'updated_at' => 'datetime',
    ];

    // Helper to get a setting or default
    public static function get($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    // Helper to set a setting
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'updated_at' => now(),
            ]
        );
    }
}
