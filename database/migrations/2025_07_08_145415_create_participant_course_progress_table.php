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
        Schema::create('participant_course_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade');
            $table->unsignedBigInteger('course_batch_id')->nullable();
            $table->date('enrollment_date');
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->enum('status', ['enrolled', 'active', 'completed', 'dropped', 'paused'])->default('enrolled');
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('notes')->nullable();

            // Test tracking fields
            $table->integer('total_tests')->default(20);
            $table->integer('tests_taken')->default(0);
            $table->integer('tests_passed')->default(0);
            $table->decimal('average_score', 5, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_course_progress');
    }
};
