use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Check if table exists
if (Schema::hasTable('participant_course_progress')) {
    echo "Table participant_course_progress exists\n";
    
    // Get current columns
    $columns = Schema::getColumnListing('participant_course_progress');
    echo "Current columns: " . implode(', ', $columns) . "\n";
    
    // Check for test tracking columns
    $needsColumns = [];
    if (!in_array('total_tests', $columns)) $needsColumns[] = 'total_tests';
    if (!in_array('tests_taken', $columns)) $needsColumns[] = 'tests_taken';
    if (!in_array('tests_passed', $columns)) $needsColumns[] = 'tests_passed';
    if (!in_array('average_score', $columns)) $needsColumns[] = 'average_score';
    
    if (empty($needsColumns)) {
        echo "All test tracking columns already exist!\n";
    } else {
        echo "Missing columns: " . implode(', ', $needsColumns) . "\n";
        echo "Adding missing columns...\n";
        
        // Add columns using raw SQL
        $sql = "ALTER TABLE participant_course_progress ";
        $alterStatements = [];
        
        if (in_array('total_tests', $needsColumns)) {
            $alterStatements[] = "ADD COLUMN total_tests INT DEFAULT 20 COMMENT 'Total tests in the course'";
        }
        if (in_array('tests_taken', $needsColumns)) {
            $alterStatements[] = "ADD COLUMN tests_taken INT DEFAULT 0 COMMENT 'Number of tests taken'";
        }
        if (in_array('tests_passed', $needsColumns)) {
            $alterStatements[] = "ADD COLUMN tests_passed INT DEFAULT 0 COMMENT 'Number of tests passed'";
        }
        if (in_array('average_score', $needsColumns)) {
            $alterStatements[] = "ADD COLUMN average_score DECIMAL(5,2) NULL COMMENT 'Average test score'";
        }
        
        $sql .= implode(', ', $alterStatements);
        
        try {
            DB::statement($sql);
            echo "Columns added successfully!\n";
            
            // Verify
            $newColumns = Schema::getColumnListing('participant_course_progress');
            echo "Updated columns: " . implode(', ', $newColumns) . "\n";
        } catch (Exception $e) {
            echo "Error adding columns: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "Table participant_course_progress does not exist!\n";
}
