<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Listing all tables in the database:\n";

try {
    $tables = DB::select('SHOW TABLES');
    
    if (empty($tables)) {
        echo "No tables found!\n";
    } else {
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            echo "- $tableName\n";
        }
    }
    
    echo "\nChecking for participant_course_progress table specifically:\n";
    $result = DB::select("SHOW TABLES LIKE 'participant_course_progress'");
    if (empty($result)) {
        echo "❌ Table 'participant_course_progress' does NOT exist\n";
        echo "This is why the courseProgress() method is failing!\n";
    } else {
        echo "✅ Table 'participant_course_progress' exists\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
