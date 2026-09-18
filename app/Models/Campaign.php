<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tracking_code',
        'referral_link',
        'commission_type',
        'type',
        'status',
        'comm_percent',
        'limit_mode',
        'limit_days',
        'limit_count',
        'clicks',
        'registrations',
        'orders',
        'total_commission',
        'balance',
        'withdrawn',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'campaign_id');
    }

    public function logs()
    {
        return $this->hasMany(CampaignLog::class, 'campaign_id');
    }
}
