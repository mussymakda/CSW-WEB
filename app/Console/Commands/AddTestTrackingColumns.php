<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTestTrackingColumns extends Command
{
    protected $signature = 'db:add-test-columns';

    protected $description = 'Add test tracking columns to participant_course_progress table';

    public function handle()
    {
        try {
            // Check if table exists
            if (! Schema::hasTable('participant_course_progress')) {
                $this->error('Table participant_course_progress does not exist!');

                return 1;
            }

            // Check current columns
            $columns = Schema::getColumnListing('participant_course_progress');
            $this->info('Current columns: '.implode(', ', $columns));

            // Check if test tracking columns exist
            $hasTestColumns = Schema::hasColumns('participant_course_progress', [
                'total_tests', 'tests_taken', 'tests_passed', 'average_score',
            ]);

            if ($hasTestColumns) {
                $this->info('Test tracking columns already exist!');

                return 0;
            }

            $this->info('Adding test tracking columns...');

            Schema::table('participant_course_progress', function (Blueprint $table) {
                if (! Schema::hasColumn('participant_course_progress', 'total_tests')) {
                    $table->integer('total_tests')->default(20)->comment('Total tests in the course');
                }
                if (! Schema::hasColumn('participant_course_progress', 'tests_taken')) {
                    $table->integer('tests_taken')->default(0)->comment('Number of tests taken');
                }
                if (! Schema::hasColumn('participant_course_progress', 'tests_passed')) {
                    $table->integer('tests_passed')->default(0)->comment('Number of tests passed');
                }
                if (! Schema::hasColumn('participant_course_progress', 'average_score')) {
                    $table->decimal('average_score', 5, 2)->nullable()->comment('Average test score');
                }
            });

            $this->info('Test tracking columns added successfully!');

            // Verify the columns were added
            $newColumns = Schema::getColumnListing('participant_course_progress');
            $this->info('Updated columns: '.implode(', ', $newColumns));

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return 1;
        }
    }
}
