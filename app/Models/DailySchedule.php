<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'task',
        'time',
        'day',
        'is_completed',
        'completed_at',
        'completion_notes',
        'priority',
        'category',
        'location',
    ];

    protected $casts = [
        'time' => 'datetime:H:i',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
