<?php

namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database extends PDO {
    /**
     * Constructor - establishes database connection
     */
    public function __construct() {
        try {
            // Try to load .env file manually
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (strpos($line, '#') === 0) {
                        continue; // Skip comments
                    }
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    putenv("$key=$value");
                }
                error_log('Loaded environment variables from .env file');
            }
            
            // Load configuration from environment variables or use defaults with multiple fallbacks
            $host = getenv('DB_HOST');
            if (!$host) {
                $host = getenv('DB_HOST') ?: 'localhost';
            }
            
            $dbname = getenv('DB_DATABASE');
            if (!$dbname) {
                $dbname = getenv('DB_NAME') ?: 'cat_db';
            }
            
            $username = getenv('DB_USERNAME');
            if (!$username) {
                $username = getenv('DB_USER') ?: 'root';
            }
            
            $password = getenv('DB_PASSWORD');
            if (!$password) {
                $password = getenv('DB_PASS') ?: '';
            }
            
            // Debug connection info
            error_log("Attempting DB connection - Host: $host, Database: $dbname, Username: $username");
            
            // Try PDO connection
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            // Call the parent PDO constructor
            parent::__construct($dsn, $username, $password, $options);
            
            error_log("Database connection successful");
        } catch (PDOException $e) {
            // Log the detailed error including connection parameters (for development only)
            error_log('Database connection error: ' . $e->getMessage());
            
            // More helpful error messages based on error type
            if (strpos($e->getMessage(), "Unknown database") !== false) {
                throw new Exception("Database '$dbname' does not exist. Please create it first.");
            } elseif (strpos($e->getMessage(), "Access denied") !== false) {
                throw new Exception("Database access denied. Check username/password for '$username'@'$host'");
            } elseif (strpos($e->getMessage(), "Connection refused") !== false) {
                throw new Exception("Connection to MySQL server at '$host' was refused. Is the server running?");
            } else {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            }
        }
    }
} 