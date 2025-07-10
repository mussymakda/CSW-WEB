<?php

echo "Creating MySQL database 'csw'...\n";

try {
    // Connect to MySQL without specifying a database
    $host = '127.0.0.1';
    $port = '3306';
    $username = 'root';
    $password = '';
    
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL server successfully!\n";
    
    // Create the database
    $sql = "CREATE DATABASE IF NOT EXISTS csw CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    
    echo "Database 'csw' created successfully!\n";
    
    // Test connection to the new database
    $dsn = "mysql:host=$host;port=$port;dbname=csw;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    
    echo "Connection to 'csw' database verified!\n";
    echo "Setup complete! You can now run: php artisan migrate\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nPossible solutions:\n";
    echo "1. Make sure MySQL server is running\n";
    echo "2. Check if MySQL is installed (try: mysql --version)\n";
    echo "3. Verify MySQL credentials in .env file\n";
    echo "4. Install MySQL if not installed: https://dev.mysql.com/downloads/mysql/\n";
}
