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
        Schema::create('video_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->foreignId('workout_video_id')->constrained()->onDelete('cascade');
            $table->timestamp('viewed_at');
            $table->integer('duration_watched_seconds')->default(0);
            $table->timestamps();

            // Ensure one view record per participant per video per day
            $table->unique(['participant_id', 'workout_video_id', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_views');
    }
};
