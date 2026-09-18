<?php

namespace App\Models;

use App\Traits\HasDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usdt extends Model
{
    use HasFactory, HasDomain;

    protected $table = 'usdt';
    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'transaction_id',
        'trans_id',
        'user_id',
        'username',
        'domain',
        'amount',
        'balance_before',
        'balance_after',
        'content',
        'bank_code',
        'transaction_date',
        'status',
    ];

    protected $appends = [
        'status_html',
        'domain_display',
        'prefix',
        'user_domain',
    ];

    public function getPrefixAttribute()
    {
        return '+';
    }

    public function getUserDomainAttribute()
    {
        return $this->user?->domain ?? $this->domain ?? 'N/A';
    }

    public function getStatusHtmlAttribute()
    {
        // Simple status helper or just return string
        return '<span class="badge badge-' . ($this->status == 'completed' ? 'success' : 'warning') . '">' . $this->status . '</span>';
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id', 'id');
    }
}
