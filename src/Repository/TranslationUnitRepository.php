<?php

namespace App\Repository;

use PDO;
use PDOException;
use InvalidArgumentException;
use RuntimeException;

require_once __DIR__ . '/../../database/db.php'; 

use Database;

class TranslationUnitRepository {
    private PDO $conn;

    public function __construct() {
        $this->conn = \Database::getConnection();
    }

    public function addUnit(string $sourceText, int $sourceLangId): int {
        if (empty(trim($sourceText))) {
            throw new InvalidArgumentException("Source text cannot be empty.");
        }

        try {
            $stmt = $this->conn->prepare("INSERT INTO translation_units (source_text, source_lang_id) VALUES (?, ?)");
            $stmt->execute([$sourceText, $sourceLangId]);
            return (int)$this->conn->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to add translation unit: " . $e->getMessage());
        }
    }

    public function getAllUnits(): array {
        try {
            $stmt = $this->conn->query("
                SELECT tu.*, t.id AS translation_id, t.translated_text, t.version, t.target_lang_id
                FROM translation_units tu
                LEFT JOIN translations t ON tu.id = t.unit_id
                ORDER BY tu.id DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to retrieve translation units: " . $e->getMessage());
        }
    }

    public function getUnitById(int $id): array {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM translation_units WHERE id = ?");
            $stmt->execute([$id]);
            $unit = $stmt->fetch(PDO::FETCH_ASSOC);
            return $unit ?: [];
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to retrieve translation unit: " . $e->getMessage());
        }
    }

    public function updateTranslation(int $unitId, int $targetLangId, string $newText): void {
        if (empty(trim($newText))) {
            throw new InvalidArgumentException("Translated text cannot be empty.");
        }

        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM translations 
                WHERE unit_id = ? AND target_lang_id = ?
            ");
            $stmt->execute([$unitId, $targetLangId]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($current) {
                $this->conn->prepare("
                    INSERT INTO translation_history (translation_id, previous_text, version)
                    VALUES (?, ?, ?)
                ")->execute([$current['id'], $current['translated_text'], $current['version']]);

                $this->conn->prepare("
                    UPDATE translations 
                    SET translated_text = ?, version = version + 1 
                    WHERE id = ?
                ")->execute([$newText, $current['id']]);
            } else {
                $this->conn->prepare("
                    INSERT INTO translations (unit_id, target_lang_id, translated_text, version)
                    VALUES (?, ?, ?, 1)
                ")->execute([$unitId, $targetLangId, $newText]);
            }
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to update translation: " . $e->getMessage());
        }
    }

    public function updateSourceText(int $unitId, string $newText): void {
        if (empty(trim($newText))) {
            throw new InvalidArgumentException("Source text cannot be empty.");
        }

        try {
            $stmt = $this->conn->prepare("
                UPDATE translation_units
                SET source_text = ?
                WHERE id = ?
            ");
            $stmt->execute([$newText, $unitId]);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to update source text: " . $e->getMessage());
        }
    }

    public function getTranslationHistory(int $translationId): array {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM translation_history 
                WHERE translation_id = ? 
                ORDER BY version DESC
            ");
            $stmt->execute([$translationId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to retrieve translation history: " . $e->getMessage());
        }
    }

    public function deleteUnit(int $id): void {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM translations WHERE unit_id = ?");
            $stmt->execute([$id]);
            $translationIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($translationIds)) {
                $in = str_repeat('?,', count($translationIds) - 1) . '?';
                $this->conn->prepare("DELETE FROM translation_history WHERE translation_id IN ($in)")
                          ->execute($translationIds);
            }

            $this->conn->prepare("DELETE FROM translations WHERE unit_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM translation_units WHERE id = ?")->execute([$id]);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to delete translation unit: " . $e->getMessage());
        }
    }
}
