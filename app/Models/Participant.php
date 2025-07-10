<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'dob',
        'profile_picture',
        'gender',
        'weight',
        'height',
        'aceds_no',
        'goal_id'
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function dailySchedules()
    {
        return $this->hasMany(DailySchedule::class);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function courseProgress()
    {
        return $this->hasMany(ParticipantCourseProgress::class);
    }
}
