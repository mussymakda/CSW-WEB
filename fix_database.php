<?php

// Direct database modification script
require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel environment
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "Checking database connection...\n";
    DB::connection()->getPdo();
    echo "Database connected successfully!\n";
    
    echo "Checking if participant_course_progress table exists...\n";
    
    if (Schema::hasTable('participant_course_progress')) {
        echo "Table exists!\n";
        
        // Get current columns
        $columns = DB::select("SHOW COLUMNS FROM participant_course_progress");
        echo "Current columns:\n";
        foreach ($columns as $column) {
            echo "- " . $column->Field . " (" . $column->Type . ")\n";
        }
        
        // Check for test tracking columns
        $columnNames = array_map(function($col) { return $col->Field; }, $columns);
        
        $missingColumns = [];
        if (!in_array('total_tests', $columnNames)) $missingColumns[] = 'total_tests';
        if (!in_array('tests_taken', $columnNames)) $missingColumns[] = 'tests_taken';
        if (!in_array('tests_passed', $columnNames)) $missingColumns[] = 'tests_passed';
        if (!in_array('average_score', $columnNames)) $missingColumns[] = 'average_score';
        
        if (empty($missingColumns)) {
            echo "\nAll test tracking columns already exist!\n";
        } else {
            echo "\nMissing columns: " . implode(', ', $missingColumns) . "\n";
            echo "Adding missing columns...\n";
            
            // Add columns one by one
            if (in_array('total_tests', $missingColumns)) {
                DB::statement("ALTER TABLE participant_course_progress ADD COLUMN total_tests INT DEFAULT 20 COMMENT 'Total tests in the course'");
                echo "Added total_tests column\n";
            }
            
            if (in_array('tests_taken', $missingColumns)) {
                DB::statement("ALTER TABLE participant_course_progress ADD COLUMN tests_taken INT DEFAULT 0 COMMENT 'Number of tests taken'");
                echo "Added tests_taken column\n";
            }
            
            if (in_array('tests_passed', $missingColumns)) {
                DB::statement("ALTER TABLE participant_course_progress ADD COLUMN tests_passed INT DEFAULT 0 COMMENT 'Number of tests passed'");
                echo "Added tests_passed column\n";
            }
            
            if (in_array('average_score', $missingColumns)) {
                DB::statement("ALTER TABLE participant_course_progress ADD COLUMN average_score DECIMAL(5,2) NULL COMMENT 'Average test score'");
                echo "Added average_score column\n";
            }
            
            echo "\nColumns added successfully!\n";
            
            // Verify the changes
            $newColumns = DB::select("SHOW COLUMNS FROM participant_course_progress");
            echo "\nUpdated table structure:\n";
            foreach ($newColumns as $column) {
                echo "- " . $column->Field . " (" . $column->Type . ")\n";
            }
        }
    } else {
        echo "Table participant_course_progress does not exist!\n";
        echo "Available tables:\n";
        $tables = DB::select("SHOW TABLES");
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            echo "- $tableName\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}
