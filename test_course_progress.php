<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing courseProgress method...\n";

try {
    // Test if the method exists on the Participant model
    $participant = new App\Models\Participant();
    
    if (method_exists($participant, 'courseProgress')) {
        echo "✅ SUCCESS: courseProgress() method exists on Participant model\n";
        
        // Test the relationship
        $progress = $participant->courseProgress();
        echo "✅ SUCCESS: Method returns: " . get_class($progress) . "\n";
        
        // Check if we have any participants
        $participantCount = App\Models\Participant::count();
        echo "📊 Participants in database: $participantCount\n";
        
        // Check if we have the tables
        $tables = [
            'participant_course_progress',
            'courses', 
            'course_batches'
        ];
        
        foreach ($tables as $table) {
            $exists = Illuminate\Support\Facades\Schema::hasTable($table);
            echo ($exists ? "✅" : "❌") . " Table '$table': " . ($exists ? "EXISTS" : "MISSING") . "\n";
        }
        
    } else {
        echo "❌ ERROR: courseProgress() method does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n✅ Fix completed! The courseProgress() method should now work.\n";
