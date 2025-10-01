<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'icon',
        'notification_text',
        'participant_id',
        'is_read',
        'notification_type',
        'delivery_time',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'delivery_time' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
