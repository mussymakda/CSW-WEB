<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'task',
        'time',
        'day',
    ];

    protected $casts = [
        'time' => 'datetime:H:i',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
