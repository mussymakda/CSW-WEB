<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_image',
    ];

    protected $appends = ['display_image_url'];

    public function getDisplayImageUrlAttribute()
    {
        return $this->display_image ? asset('storage/' . $this->display_image) : null;
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function workoutSubcategories()
    {
        return $this->belongsToMany(WorkoutSubcategory::class);
    }
}
