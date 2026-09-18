<?php

namespace App\Models;

use Helper;
use App\Traits\HasDomain;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory, HasDomain;

    protected $table = 'cards';

    const STATUS_COMPLETED = 'Completed';
    const STATUS_SUCCESS = 'success';
    const STATUS_PAID = 'paid';
    const STATUS_PENDING = 'Pending';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_ERROR = 'Error';

    protected $fillable = [
        'type',
        'card_type',
        'code',
        'serial',
        'value',
        'amount',
        'balance',
        'status',
        'user_id',
        'username',
        'domain',
        'expiry_date',
        'sys_note',
        'content',
        'order_id',
        'request_id',
        'channel_charge',
        'transaction_code',
    ];

    protected $appends = [
        'status_str',
        'status_html',
        'type',
        'amount',
        'domain_display',
    ];

    public function getStatusStrAttribute()
    {
        return Helper::formatStatus($this->status, 'text');
    }

    public function getStatusHtmlAttribute()
    {
        return Helper::formatStatus($this->status, 'html');
    }

    // --- Legacy Support Accessors & Mutators ---

    public function getTypeAttribute()
    {
        return $this->attributes['card_type'] ?? ($this->attributes['type'] ?? null);
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['card_type'] = $value;
        if (array_key_exists('type', $this->attributes)) {
            $this->attributes['type'] = $value;
        }
    }

    public function getAmountAttribute()
    {
        $amount = $this->attributes['amount'] ?? 0;
        $balance = $this->attributes['balance'] ?? 0;

        return $amount > 0 ? $amount : ($balance > 0 ? $balance : 0);
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['balance'] = $value;
        $this->attributes['amount'] = $value;
    }

    public function user()
    {
        return $this->belongsTo(User::class , 'user_id', 'id');
    }
}
