<?php

namespace CAT\Repository;

use PDO;
use CAT\Database;

class TranslationUnitRepository
{
    private $database;
    private $pdo;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->pdo = $database->getConnection();
    }
    
    public function create(string $sourceText, int $sourceLanguageId): array
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO translation_units 
            (source_text, source_language_id)
            VALUES (?, ?)
            RETURNING id
        ");
        $stmt->execute([$sourceText, $sourceLanguageId]);
        $id = $stmt->fetchColumn();
        if (!$id) return null;
        return $this->find($id);
    }

    public function find(int $unitId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                u.id, 
                u.source_text, 
                u.source_language_id,
                l.code as source_language_code,
                l.name as source_language_name,
                u.context, 
                u.created_at
            FROM translation_units u
            JOIN languages l ON u.source_language_id = l.id
            WHERE u.id = ?
        ");
        $stmt->execute([$unitId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function update(int $unitId, string $sourceText): ?array
    {
        $stmt = $this->pdo->prepare("
            UPDATE translation_units
            SET source_text = ?
            WHERE id = ?
        ");
        $success = $stmt->execute([$sourceText, $unitId]);
        if (!$success) return null;
        return $this->find($unitId);
    }

    public function delete(int $unitId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM translation_units WHERE id = ?");
        return $stmt->execute([$unitId]);
    }

    public function findAll(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare("
            SELECT 
                u.id, 
                u.source_text, 
                u.source_language_id,
                l.code as source_language_code,
                u.created_at
            FROM translation_units u
            JOIN languages l ON u.source_language_id = l.id
            ORDER BY u.id
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}