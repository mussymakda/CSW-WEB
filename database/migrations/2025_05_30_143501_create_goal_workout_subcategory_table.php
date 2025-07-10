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
        Schema::create('goal_workout_subcategory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goal_id');
            $table->unsignedBigInteger('workout_subcategory_id');
            $table->foreign('goal_id')->references('id')->on('goals')->onDelete('cascade');
            $table->foreign('workout_subcategory_id')->references('id')->on('workout_subcategories')->onDelete('cascade');
            $table->unique(['goal_id', 'workout_subcategory_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_workout_subcategory');
    }
};
