<?php

/**
 * Dynamic Database Setup Script for CSW Project
 * This script creates the database specified in your .env file
 */

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    $envLines = explode("\n", $envContent);
    
    foreach ($envLines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
} else {
    echo "Error: .env file not found. Please create one from .env.example\n";
    exit(1);
}

// Get database configuration from environment
$connection = getenv('DB_CONNECTION') ?: 'mysql';
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'csw';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$driver = getenv('DB_DRIVER') ?: $connection;

echo "Setting up database with the following configuration:\n";
echo "Connection: $connection\n";
echo "Driver: $driver\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? '(empty)' : '(set)') . "\n\n";

try {
    if ($driver === 'sqlite') {
        // For SQLite, just check if we can create/access the file
        $dbPath = $database;
        if (!str_contains($dbPath, '/') && !str_contains($dbPath, '\\')) {
            $dbPath = __DIR__ . '/database/' . $database;
        }
        
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "Created directory: $dir\n";
        }
        
        $pdo = new PDO("sqlite:$dbPath");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "SQLite database created/verified: $dbPath\n";
        
    } else {
        // For MySQL/PostgreSQL/MariaDB - create database if it doesn't exist
        if ($driver === 'pgsql') {
            $dsn = "pgsql:host=$host;port=$port";
            $createSql = "CREATE DATABASE \"$database\"";
        } else {
            // MySQL/MariaDB
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $createSql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        }
        
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "Connected to $driver server successfully!\n";
        
        // Create the database
        $pdo->exec($createSql);
        echo "Database '$database' created successfully!\n";
        
        // Test connection to the new database
        if ($driver === 'pgsql') {
            $testDsn = "pgsql:host=$host;port=$port;dbname=$database";
        } else {
            $testDsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
        }
        
        $testPdo = new PDO($testDsn, $username, $password);
        echo "Connection to '$database' database verified!\n";
    }
    
    echo "\n✅ Database setup complete!\n";
    echo "\nNext steps:\n";
    echo "1. Generate application key: php artisan key:generate\n";
    echo "2. Run migrations: php artisan migrate\n";
    echo "3. Seed database: php artisan db:seed\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nPossible solutions:\n";
    
    if ($driver === 'sqlite') {
        echo "1. Check if the directory for SQLite database exists and is writable\n";
        echo "2. Verify the DB_DATABASE path in .env file\n";
    } else {
        echo "1. Make sure $driver server is running\n";
        echo "2. Check if $driver is installed (try: mysql --version or psql --version)\n";
        echo "3. Verify $driver credentials in .env file\n";
        echo "4. Check if the $driver service is started\n";
        
        if ($driver === 'mysql') {
            echo "5. Install MySQL if not installed: https://dev.mysql.com/downloads/mysql/\n";
            echo "6. For XAMPP users: Start MySQL from XAMPP Control Panel\n";
        } elseif ($driver === 'pgsql') {
            echo "5. Install PostgreSQL if not installed: https://www.postgresql.org/download/\n";
        }
    }
    
    exit(1);
}
