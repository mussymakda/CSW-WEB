<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkoutVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'duration_minutes',
        'video_url',
        'workout_subcategory_id',
    ];

    protected $appends = ['image_url', 'duration_formatted'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getDurationFormattedAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        return $minutes . 'm';
    }

    public function workoutSubcategory()
    {
        return $this->belongsTo(WorkoutSubcategory::class);
    }
}
