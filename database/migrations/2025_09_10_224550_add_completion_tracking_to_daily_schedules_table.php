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
        Schema::table('daily_schedules', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('day');
            $table->timestamp('completed_at')->nullable()->after('is_completed');
            $table->text('completion_notes')->nullable()->after('completed_at');
            $table->integer('priority')->default(1)->after('completion_notes'); // 1-5, 1 being highest
            $table->string('category')->nullable()->after('priority'); // work, personal, health, etc.
            $table->string('location')->nullable()->after('category'); // for smart routing suggestions
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'is_completed',
                'completed_at', 
                'completion_notes',
                'priority',
                'category',
                'location'
            ]);
        });
    }
};
