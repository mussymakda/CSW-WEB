<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_weeks',
        'difficulty_level'
    ];

    public function batches()
    {
        return $this->hasMany(CourseBatch::class);
    }
}
