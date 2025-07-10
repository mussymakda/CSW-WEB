<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkoutSubcategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'info',
        'image',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function goals()
    {
        return $this->belongsToMany(Goal::class);
    }

    public function workoutVideos()
    {
        return $this->hasMany(WorkoutVideo::class);
    }
}
