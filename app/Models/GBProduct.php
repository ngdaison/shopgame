<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Helper;

class GBProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'code',
        'descr',
        'status',
        'priority',
        'package_id',
        'warranty_hours',
    ];

    protected $casts = [
        'status' => 'boolean',
        'priority' => 'integer',
        'price' => 'integer',
        'package_id' => 'integer',
        'warranty_hours' => 'integer',
    ];

    protected $appends = [
        'payment_str',
    ];

    public function getPaymentStrAttribute()
    {
        return Helper::formatCurrency($this->price);
    }

    public function package()
    {
        return $this->belongsTo(GBPackage::class);
    }

    public static function generateCode()
    {
        $code = date('y') . date('m') . Helper::randomNumber(4);

        if (self::where('code', $code)->exists()) {
            return self::generateCode();
        }

        return $code;
    }
}
