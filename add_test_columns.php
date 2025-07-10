<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

// Bootstrap Laravel app to get database configuration
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Check if columns already exist
    $columns = Capsule::schema()->getColumnListing('participant_course_progress');
    
    echo "Current columns in participant_course_progress:\n";
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    // Check if test tracking columns exist
    $hasTestColumns = in_array('tests_taken', $columns) && 
                     in_array('tests_passed', $columns) && 
                     in_array('total_tests', $columns) && 
                     in_array('average_score', $columns);
    
    if (!$hasTestColumns) {
        echo "\nAdding test tracking columns...\n";
        
        Capsule::schema()->table('participant_course_progress', function (Blueprint $table) use ($columns) {
            if (!in_array('total_tests', $columns)) {
                $table->integer('total_tests')->default(20)->comment('Total tests in the course');
            }
            if (!in_array('tests_taken', $columns)) {
                $table->integer('tests_taken')->default(0)->comment('Number of tests taken');
            }
            if (!in_array('tests_passed', $columns)) {
                $table->integer('tests_passed')->default(0)->comment('Number of tests passed');
            }
            if (!in_array('average_score', $columns)) {
                $table->decimal('average_score', 5, 2)->nullable()->comment('Average test score');
            }
        });
        
        echo "Test tracking columns added successfully!\n";
    } else {
        echo "\nTest tracking columns already exist!\n";
    }
    
    // Verify the columns were added
    $newColumns = Capsule::schema()->getColumnListing('participant_course_progress');
    echo "\nUpdated columns in participant_course_progress:\n";
    foreach ($newColumns as $column) {
        echo "- $column\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
