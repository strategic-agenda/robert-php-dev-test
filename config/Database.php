<?php

namespace AwaisAmir\RobertPhpDevTest\Config;

require_once '../vendor/autoload.php';
use Dotenv\Dotenv;

class Database {
    private $servername;
    private $username;
    private $password;
    private $database;
    protected $conn;

    // Constructor
    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $this->servername = $_ENV['DB_HOST'];
        $this->username = $_ENV['DB_USERNAME'];
        $this->password = $_ENV['DB_PASSWORD'];
        $this->database = $_ENV['DB_NAME'];
        $this->connect();
    }

    // Connect to the database
    private function connect() {
        $this->conn = new \mysqli($this->servername, $this->username, $this->password, $this->database);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        // Set charset to UTF-8
        $this->conn->set_charset("utf8");
    }

    // Get the database connection
    public function getConnection() {
        return $this->conn;
    }

    // Close the database connection
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

