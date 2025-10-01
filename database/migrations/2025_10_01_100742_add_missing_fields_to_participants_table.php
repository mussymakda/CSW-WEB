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
            $table->text('program_description')->nullable();
            $table->enum('status', ['active', 'enrolled', 'completed', 'graduated', 'paused', 'dropped', 'inactive'])->default('active');
            $table->date('graduation_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['program_description', 'status', 'graduation_date']);
        });
    }
};
