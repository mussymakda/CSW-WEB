<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'batch_name',
        'start_date',
        'end_date',
        'max_participants',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function participantProgress()
    {
        return $this->hasMany(ParticipantCourseProgress::class);
    }
}
