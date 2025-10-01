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
            // Add new fields for better mobile app support
            $table->date('date_of_birth')->nullable()->after('dob');
            $table->decimal('weight_kg', 5, 2)->nullable()->after('weight');
            $table->integer('height_cm')->nullable()->after('height');
            $table->enum('fitness_level', ['beginner', 'intermediate', 'advanced'])->nullable()->after('height_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'weight_kg', 'height_cm', 'fitness_level']);
        });
    }
};
