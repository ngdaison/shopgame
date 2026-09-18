<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'title',
        'category',
        'status',
        'priority',
        'last_message_at',
        'last_reply_by',
        'unread_for_user',
        'unread_for_admin',
        'messages', 
        'admin_note',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'messages' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define accessor for latest message to keep compatibility or ease of use
    public function getLatestMessageAttribute()
    {
        $messages = $this->messages;
        if (empty($messages) || !is_array($messages)) {
            return null;
        }
        $last = end($messages);
        return (object) $last; // Return as object to match previous relation behavior somewhat
    }
}
