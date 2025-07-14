<?php
echo "<h1>PHP Extension Check</h1>";
echo "<h2>PHP Version:</h2>";
echo "<p>" . phpversion() . "</p>";

echo "<h2>intl Extension Status:</h2>";
if (extension_loaded('intl')) {
    echo "<p style='color: green;'>✅ intl extension is LOADED</p>";
    echo "<p>Version: " . phpversion('intl') . "</p>";
} else {
    echo "<p style='color: red;'>❌ intl extension is NOT LOADED</p>";
}

echo "<h2>All Loaded Extensions:</h2>";
$extensions = get_loaded_extensions();
sort($extensions);
foreach ($extensions as $extension) {
    echo "<li>$extension</li>";
}

echo "<h2>PHP Configuration File:</h2>";
echo "<p>" . php_ini_loaded_file() . "</p>";

echo "<h2>Extension Directory:</h2>";
echo "<p>" . ini_get('extension_dir') . "</p>";
?>
