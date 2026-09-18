<?php

namespace App\Models;

use App\Traits\HasDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory, HasDomain;

    protected $fillable = [
        'role',
        'data',
        'domain',
        'user_id',
        'content',
        'username',
        'ip_address',
    ];


    protected $casts = [
        'user_id' => 'integer',
        'username' => 'string',
        'content' => 'string',
        'role' => 'string',
        'data' => 'array',
    ];

    public function getIpAddressAttribute($value)
    {
        if (empty($value)) {
            return 'N/A';
        }

        // Check if it's a JSON array (old data)
        if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
            try {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    // Get the latest IP from the decoded array
                    return end($decoded);
                }
            }
            catch (\Exception $e) {
            // Ignore decoding errors
            }
        }

        return $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
