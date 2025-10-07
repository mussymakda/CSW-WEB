<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Participant extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_otp',
        'email_otp_expires_at',
        'email_verified_at',
        'phone',
        'password',
        'password_changed_from_default',
        'date_of_birth',
        'dob', // Keep for backward compatibility
        'profile_picture',
        'gender',
        'weight_kg',
        'height_cm',
        'weight', // Keep for backward compatibility
        'height', // Keep for backward compatibility
        'fitness_level',
        'aceds_no',
        'goal_id',
        'student_number',
        'location',
        'client_name',
        'program_description',
        'status',
        'graduation_date',
        'terms_accepted',
        'terms_accepted_at',
        'onboarding_completed',
        'onboarding_completed_at',
    ];

    protected $hidden = [
        'password',
        'email_otp',
    ];

    protected $casts = [
        'password' => 'hashed',
        'dob' => 'date',
        'date_of_birth' => 'date',
        'graduation_date' => 'date',
        'email_otp_expires_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'terms_accepted' => 'boolean',
        'onboarding_completed' => 'boolean',
        'password_changed_from_default' => 'boolean',
    ];

    protected $appends = ['profile_picture_url'];

    /**
     * Get the profile picture URL
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        return $this->profile_picture ? asset('storage/'.$this->profile_picture) : null;
    }

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

    /**
     * Many-to-many relationship with goals (participant can have multiple goals)
     */
    public function goals()
    {
        return $this->belongsToMany(Goal::class, 'participant_goal');
    }

    /**
     * Check if participant needs onboarding
     */
    public function needsOnboarding(): bool
    {
        return ! $this->onboarding_completed ||
               ! $this->terms_accepted ||
               ! $this->password_changed_from_default ||
               ! $this->email_verified_at;
    }

    /**
     * Generate OTP for email verification
     */
    public function generateEmailOtp(): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'email_otp' => $otp,
            'email_otp_expires_at' => now()->addMinutes(10), // OTP expires in 10 minutes
        ]);

        return $otp;
    }

    /**
     * Verify OTP
     */
    public function verifyEmailOtp(string $otp): bool
    {
        if ($this->email_otp !== $otp) {
            return false;
        }

        if (! $this->email_otp_expires_at || $this->email_otp_expires_at->isPast()) {
            return false;
        }

        $this->update([
            'email_verified_at' => now(),
            'email_otp' => null,
            'email_otp_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Complete onboarding process
     */
    public function completeOnboarding(): void
    {
        $this->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);
    }
}
