<?php

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Enable error reporting
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        
        $host = getenv('DB_HOST') ?: 'localhost';
        $dbName = getenv('DB_NAME') ?: 'cat_db';
        $user = getenv('DB_USER') ?: 'cat_user';
        $password = getenv('DB_PASSWORD') ?: 'cat_password';
        
        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$dbName",
                $user,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Test the connection
            $this->connection->query("SELECT 1");
        } catch (PDOException $e) {
            // Provide more detailed error information
            echo json_encode([
                'error' => 'Database Connection Error',
                'message' => $e->getMessage(),
                'config' => [
                    'host' => $host,
                    'dbName' => $dbName,
                    'user' => $user
                ]
            ]);
            die();
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
} 