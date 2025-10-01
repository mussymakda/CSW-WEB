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
            $table->index(['created_at']); // For date sorting
            $table->index(['student_number']); // For student number searches
            $table->index(['email']); // For email searches
        });

        Schema::table('daily_schedules', function (Blueprint $table) {
            $table->index(['participant_id', 'day']); // Composite index for common queries
            $table->index(['is_completed']); // For filtering completed tasks
            $table->index(['category']); // For category filtering
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            $table->index(['participant_id', 'is_read']); // Composite index for user notifications
            $table->index(['created_at']); // For date sorting
        });

        Schema::table('participant_course_progress', function (Blueprint $table) {
            $table->index(['status']); // For status filtering
            $table->index(['progress_percentage']); // For progress queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['student_number']);
            $table->dropIndex(['email']);
        });

        Schema::table('daily_schedules', function (Blueprint $table) {
            $table->dropIndex(['participant_id', 'day']);
            $table->dropIndex(['is_completed']);
            $table->dropIndex(['category']);
        });

        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropIndex(['participant_id', 'is_read']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('participant_course_progress', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['progress_percentage']);
        });
    }
};
