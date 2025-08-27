<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing date parsing without intl extension...\n\n";

// Test date formats
$testDates = [
    '7/14/2025',
    '07/14/2025', 
    '7-14-2025',
    '2025-07-14',
    '14/07/2025', // European
    '7/4/25',
];

foreach ($testDates as $dateString) {
    echo "Testing: $dateString\n";
    
    try {
        // Test with strtotime
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            echo "  ✅ strtotime: " . date('Y-m-d', $timestamp) . "\n";
        } else {
            echo "  ❌ strtotime: failed\n";
        }
        
        // Test with DateTime::createFromFormat
        $formats = ['m/d/Y', 'n/j/Y', 'm-d-Y', 'Y-m-d', 'd/m/Y', 'm/d/y'];
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                echo "  ✅ DateTime ($format): " . $date->format('Y-m-d') . "\n";
                break;
            }
        }
        
    } catch (Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Date parsing test completed!\n";
