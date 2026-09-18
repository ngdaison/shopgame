<?php

namespace App\Models;

use App\Traits\HasDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Helper;

class Banking extends Model
{
    use HasFactory, HasDomain;

    protected $table = 'banking';

    protected $fillable = [
        'trans_id',
        'user_id',
        'amount',
        'balance_before',
        'balance_after',
        'content',
        'status',
        'domain',
        'bank_code',
        'username',
    ];

    protected $appends = [
        'status_html',
        'prefix',
        'domain_display',
    ];

    public function getPrefixAttribute()
    {
        return $this->balance_before > $this->balance_after ? '-' : '+';
    }

    public function getStatusHtmlAttribute()
    {
        return Helper::formatStatus($this->status, 'html');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
