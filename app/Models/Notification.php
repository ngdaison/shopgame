<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'code',
        'type',
        'title',
        'content',
        'body',
        'icon',
        'link',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = strtoupper(\Illuminate\Support\Str::random(10));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
