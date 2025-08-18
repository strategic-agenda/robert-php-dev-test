<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/TranslationUnit.php';

class TranslationUnitManager
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::connect();
    }

    public function addUnit(string $source, string $translated, string $from, string $to): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO translations (source_text, translated_text, language_from, language_to) VALUES (?, ?, ?, ?)");
        $stmt->execute([$source, $translated, $from, $to]);
        return $this->pdo->lastInsertId();
    }

    public function getUnit(int $id): ?TranslationUnit
    {
        $stmt = $this->pdo->prepare("SELECT * FROM translations WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row) return null;

        return new TranslationUnit(
            $row['id'],
            $row['source_text'],
            $row['translated_text'],
            $row['language_from'],
            $row['language_to']
        );
    }

    public function updateUnit(int $id, string $newSourceText, string $newTranslation): bool
    {
        $this->addHistory($id, $newSourceText, $newTranslation);

        $stmt = $this->pdo->prepare("UPDATE translations SET source_text = ?, translated_text = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$newSourceText, $newTranslation, $id]);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM translations ORDER BY id DESC LIMIT 10");
        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new TranslationUnit(
                $row['id'],
                $row['source_text'],
                $row['translated_text'],
                $row['language_from'],
                $row['language_to']
            );
        }

        return $results;
    }

    public function getAllAsArray(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM translations ORDER BY id DESC LIMIT 10");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function addHistory(int $id, string $newSourceText, string $newTranslation) 
    {
        $stmt = $this->pdo->prepare("SELECT source_text, translated_text FROM translations WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($result) $old = $result[0];
        else return false;

        $stmt = $this->pdo->prepare("INSERT INTO translation_history (translation_unit_id, old_source_text, new_source_text, old_translated_text, new_translated_text) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$id, $old['source_text'], $newSourceText, $old['translated_text'], $newTranslation]);
    }

    public function getHistoryByUnitId(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM translation_history WHERE translation_unit_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
