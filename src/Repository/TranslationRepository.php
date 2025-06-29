<?php

namespace CAT\Repository;

use PDO;
use CAT\Database;

class TranslationRepository
{
    private $database;
    private $pdo;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->pdo = $database->getConnection();
    }

    public function create(int $unitId, int $targetLanguageId, string $translatedText): ?array
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO translations 
            (unit_id, target_language_id, translated_text, updated_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            RETURNING id
        ");
        $stmt->execute([$unitId, $targetLanguageId, $translatedText]);
        $id = $stmt->fetchColumn();
        if (!$id) return null;
        return $this->find($id);
    }

    public function find(int $translationId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                t.id,
                t.unit_id,
                t.target_language_id
                l.code as target_language_code,
                l.name as target_language_name,
                t.translated_text,
                t.created_at,
                t.updated_at,
                u.source_text
            FROM translations t
            JOIN languages l ON t.target_language_id = l.id
            JOIN translation_units u ON t.unit_id = u.id
            WHERE t.id = ?
        ");
        $stmt->execute([$translationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function update(int $translationId, string $translatedText): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE translations
            SET translated_text = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
            RETURNING *
        ");
        $stmt->execute([$translatedText, $translationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByUnit(int $unitId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                t.id,
                t.target_language_id,
                l.code as target_language_code,
                l.name as target_language_name,
                t.translated_text,
                t.created_at,
                t.updated_at
            FROM translations t
            JOIN languages l ON t.target_language_id = l.id
            WHERE t.unit_id = ?
            ORDER BY l.code
        ");
        $stmt->execute([$unitId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByLanguage(int $languageId, int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $this->pdo->prepare("
            SELECT 
                t.id,
                t.unit_id,
                t.translated_text,
                t.updated_at,
                u.source_text
            FROM translations t
            JOIN translation_units u ON t.unit_id = u.id
            WHERE t.target_language_id = ?
            ORDER BY t.updated_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $languageId, PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $translationId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM translations WHERE id = ?");
        return $stmt->execute([$translationId]);
    }
}