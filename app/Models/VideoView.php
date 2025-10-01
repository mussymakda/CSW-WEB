<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoView extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'workout_video_id',
        'viewed_at',
        'duration_watched_seconds',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function workoutVideo()
    {
        return $this->belongsTo(WorkoutVideo::class);
    }
}
