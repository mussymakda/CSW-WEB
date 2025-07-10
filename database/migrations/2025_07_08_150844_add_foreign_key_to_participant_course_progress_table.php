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
            $table->foreign('course_batch_id')->references('id')->on('course_batches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_course_progress', function (Blueprint $table) {
            $table->dropForeign(['course_batch_id']);
        });
    }
};
