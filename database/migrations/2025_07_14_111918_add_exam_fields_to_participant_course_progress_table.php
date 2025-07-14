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
        Schema::table('participant_course_progress', function (Blueprint $table) {
            $table->integer('total_exams')->nullable()->after('notes');
            $table->integer('exams_taken')->nullable()->after('total_exams');
            $table->integer('exams_needed')->nullable()->after('exams_taken');
            $table->date('last_exam_date')->nullable()->after('exams_needed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_course_progress', function (Blueprint $table) {
            $table->dropColumn(['total_exams', 'exams_taken', 'exams_needed', 'last_exam_date']);
        });
    }
};
