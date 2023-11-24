<?php
namespace Lampdev\RobertPhpDevTest;

use Dotenv\Dotenv;
use PDO;

class Database {
    private PDO $conn;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $host = $_ENV['DB_HOST'];
        $dbName = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        $this->conn = new PDO(
            "mysql:host={$host};dbname={$dbName}",
            $username,
            $password
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO {
        return $this->conn;
    }
}
