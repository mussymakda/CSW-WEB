<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            // Split name into first_name and last_name
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            
            // OTP verification fields
            $table->string('email_otp')->nullable()->after('email');
            $table->timestamp('email_otp_expires_at')->nullable()->after('email_otp');
            $table->timestamp('email_verified_at')->nullable()->after('email_otp_expires_at');
            
            // Terms and conditions
            $table->boolean('terms_accepted')->default(false)->after('email_verified_at');
            $table->timestamp('terms_accepted_at')->nullable()->after('terms_accepted');
            
            // Onboarding status
            $table->boolean('onboarding_completed')->default(false)->after('terms_accepted_at');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_completed');
            
            // Additional profile fields (if not exists)
            // phone, dob, gender, weight, height already exist
            
            // Password reset tracking
            $table->boolean('password_changed_from_default')->default(false)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'email_otp',
                'email_otp_expires_at',
                'email_verified_at',
                'terms_accepted',
                'terms_accepted_at',
                'onboarding_completed',
                'onboarding_completed_at',
                'password_changed_from_default',
            ]);
        });
    }
};
