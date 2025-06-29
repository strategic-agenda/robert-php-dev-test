<?php
namespace CAT\Controller;

use CAT\Database;

class LanguageController
{
    private $db;
    private $pdo;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->pdo = $db->getConnection();
    }

    public function list(): void
    {
        $stmt = $this->pdo->query('SELECT id, code, name FROM languages ORDER BY name');
        $languages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        sendJson($languages);
    }
} 