<?php

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        $config = require __DIR__ . '/../config/config.test.php';

        $this->pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']}",
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );

        $this->cleanDatabase();
        $this->seedLanguages();
    }

    protected function cleanDatabase(): void
    {
        $this->pdo->exec("DELETE FROM translation_history");
        $this->pdo->exec("DELETE FROM translations");
        $this->pdo->exec("DELETE FROM translation_unit_history");
        $this->pdo->exec("DELETE FROM translation_units");
        $this->pdo->exec("DELETE FROM languages");
    }

    protected function seedLanguages(): void
    {
        $this->pdo->exec("
            INSERT INTO languages (id, name) VALUES 
            (1, 'English') ON DUPLICATE KEY UPDATE name = 'English';
        ");
        $this->pdo->exec("
            INSERT INTO languages (id, name) VALUES 
            (2, 'French') ON DUPLICATE KEY UPDATE name = 'French';
        ");
    }
}
