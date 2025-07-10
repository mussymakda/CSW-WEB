<?php

echo "Testing database setup...\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "Laravel loaded successfully\n";
    
    // Test database connection
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "Database connection: OK\n";
    
    // Check if table exists
    $result = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'participant_course_progress'");
    if (empty($result)) {
        echo "ERROR: Table 'participant_course_progress' does not exist!\n";
        
        // Show all tables
        $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES");
        echo "Available tables:\n";
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            echo "  - $tableName\n";
        }
    } else {
        echo "Table 'participant_course_progress' exists\n";
        
        // Check columns
        $columns = \Illuminate\Support\Facades\DB::select("DESCRIBE participant_course_progress");
        echo "Columns:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field} ({$column->Type})\n";
        }
        
        // Check for test columns specifically
        $testColumns = ['total_tests', 'tests_taken', 'tests_passed', 'average_score'];
        $existingColumns = array_map(function($col) { return $col->Field; }, $columns);
        
        $missing = array_diff($testColumns, $existingColumns);
        if (!empty($missing)) {
            echo "Missing test tracking columns: " . implode(', ', $missing) . "\n";
            echo "Adding them now...\n";
            
            foreach ($missing as $col) {
                switch ($col) {
                    case 'total_tests':
                        \Illuminate\Support\Facades\DB::statement("ALTER TABLE participant_course_progress ADD COLUMN total_tests INT DEFAULT 20");
                        break;
                    case 'tests_taken':
                        \Illuminate\Support\Facades\DB::statement("ALTER TABLE participant_course_progress ADD COLUMN tests_taken INT DEFAULT 0");
                        break;
                    case 'tests_passed':
                        \Illuminate\Support\Facades\DB::statement("ALTER TABLE participant_course_progress ADD COLUMN tests_passed INT DEFAULT 0");
                        break;
                    case 'average_score':
                        \Illuminate\Support\Facades\DB::statement("ALTER TABLE participant_course_progress ADD COLUMN average_score DECIMAL(5,2) NULL");
                        break;
                }
                echo "Added column: $col\n";
            }
            
            echo "All test tracking columns added!\n";
        } else {
            echo "All test tracking columns exist!\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "In file: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}

echo "Test completed.\n";
