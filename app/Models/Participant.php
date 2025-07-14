<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'dob',
        'profile_picture',
        'gender',
        'weight',
        'height',
        'aceds_no',
        'goal_id',
        'student_number',
        'location',
        'client_name',
        'program_description',
        'status',
        'graduation_date',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'dob' => 'date',
        'graduation_date' => 'date',
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
