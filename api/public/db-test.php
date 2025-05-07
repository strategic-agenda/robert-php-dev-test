<?php
header('Content-Type: text/plain');
echo "Database Connection Test\n";
echo "======================\n\n";

// Check environment variables
echo "Environment Variables:\n";
echo "---------------------\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'Not Set') . "\n";
echo "DB_DATABASE: " . (getenv('DB_DATABASE') ?: 'Not Set') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'Not Set') . "\n";
echo "DB_USERNAME: " . (getenv('DB_USERNAME') ?: 'Not Set') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'Not Set') . "\n";
echo "DB_PASSWORD: " . (getenv('DB_PASSWORD') ? 'Set (hidden)' : 'Not Set') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ? 'Set (hidden)' : 'Not Set') . "\n";
echo "\n";

// Check if environment loading is working
echo "PHP Info:\n";
echo "---------\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "\n\n";

// Try to connect directly
echo "Connection Test:\n";
echo "---------------\n";
try {
    // Try with DB_HOST, DB_DATABASE, DB_USERNAME
    $host = getenv('DB_HOST') ?: 'localhost';
    $dbname = getenv('DB_DATABASE') ?: 'cat_db';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    
    echo "Trying primary connection variables:\n";
    echo "Host: $host\n";
    echo "Database: $dbname\n";
    echo "Username: $username\n";
    echo "Password: " . (empty($password) ? "Not Set" : "Set (hidden)") . "\n\n";
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "SUCCESS! Connected to database with primary variables.\n\n";
} catch (PDOException $e) {
    echo "FAILED with primary variables: " . $e->getMessage() . "\n\n";
    
    // Try with fallback variables
    try {
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'cat_db';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') ?: '';
        
        echo "Trying fallback connection variables:\n";
        echo "Host: $host\n";
        echo "Database: $dbname\n";
        echo "Username: $username\n";
        echo "Password: " . (empty($password) ? "Not Set" : "Set (hidden)") . "\n\n";
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "SUCCESS! Connected to database with fallback variables.\n\n";
    } catch (PDOException $e) {
        echo "FAILED with fallback variables: " . $e->getMessage() . "\n\n";
    }
}

// Test if database class is accessible
echo "Database Class Test:\n";
echo "------------------\n";
try {
    require_once __DIR__ . '/../src/Database.php';
    echo "Database class file exists and can be included.\n";
    
    $db = new Database();
    echo "SUCCESS! Database class instantiated successfully.\n";
} catch (Exception $e) {
    echo "FAILED to use Database class: " . $e->getMessage() . "\n";
}

echo "\n======================\n";
echo "End of Test\n"; 