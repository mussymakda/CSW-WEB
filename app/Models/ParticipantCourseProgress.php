<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantCourseProgress extends Model
{
    use HasFactory;

    protected $table = 'participant_course_progress';

    protected $fillable = [
        'participant_id',
        'course_batch_id',
        'enrollment_date',
        'started_at',
        'completed_at',
        'progress_percentage',
        'status',
        'grade',
        'notes',
        'total_tests',
        'tests_taken',
        'tests_passed',
        'average_score',
        'total_exams',
        'exams_taken',
        'exams_needed',
        'last_exam_date',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'started_at' => 'date',
        'completed_at' => 'date',
        'last_exam_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'grade' => 'decimal:2',
        'average_score' => 'decimal:2',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function courseBatch()
    {
        return $this->belongsTo(CourseBatch::class);
    }

    // Calculate test progress percentage
    public function getTestProgressAttribute()
    {
        if ($this->total_tests == 0) {
            return 0;
        }

        return ($this->tests_passed / $this->total_tests) * 100;
    }

    // Calculate time progress percentage
    public function getTimeProgressAttribute()
    {
        if (! $this->started_at || ! $this->courseBatch) {
            return 0;
        }

        $courseDays = $this->courseBatch->course->duration_weeks * 7;
        $daysElapsed = now()->diffInDays($this->started_at);

        return min(($daysElapsed / $courseDays) * 100, 100);
    }

    // Calculate overall progress (70% test, 30% time)
    public function calculateOverallProgress()
    {
        $testProgress = $this->test_progress;
        $timeProgress = $this->time_progress;

        return ($testProgress * 0.7) + ($timeProgress * 0.3);
    }
}
