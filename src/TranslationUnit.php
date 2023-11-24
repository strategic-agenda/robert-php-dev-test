<?php

namespace Lampdev\RobertPhpDevTest;

use Exception;
use PDO;

readonly class TranslationUnit
{
    public function __construct(
        private PDO $db
    ) {
    }

    /**
     * @return false|array
     */
    public function getAllTranslationUnits(): false|array
    {
        $query = "SELECT * FROM translation_units";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param  string  $text
     * @param  int  $languageId
     * @return array|null
     */
    public function addTranslationUnit(string $text, int $languageId): ?array
    {
        $stmt = $this->db->prepare(
            "INSERT INTO translation_units (source_text, language_id) VALUES (?, ?)"
        );
        $stmt->execute([$text, $languageId]);
        return $this->getTranslationUnit((int)$this->db->lastInsertId());
    }

    /**
     * @param  int  $id
     * @param  string  $newText
     * @return void
     * @throws Exception
     */
    public function updateTranslationUnit(int $id, string $newText): void
    {
        $this->db->beginTransaction();
        try {
            // Retrieve the latest version for this unit
            $stmt = $this->db->prepare(
                "SELECT MAX(version) AS latest_version FROM translation_versions WHERE translation_unit_id = ?"
            );
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $currentVersion = $result['latest_version'] ?? 1;
            $currentData = $this->getTranslationUnit($id);
            $currentText = $currentData['source_text'] ?? '';

            // Insert into history
            $stmt = $this->db->prepare(
                "INSERT INTO translation_versions (translation_unit_id, translated_text, version) VALUES (?, ?, ?)"
            );
            $stmt->execute([$id, $currentText, $currentVersion + 1]);

            $stmt = $this->db->prepare(
                "UPDATE translation_units SET source_text = ? WHERE id = ?"
            );
            $stmt->execute([$newText, $id]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * @param  int  $id
     * @return array|null
     */
    public function getTranslationUnit(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM translation_units WHERE id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * @param  int  $id
     * @return void
     * @throws Exception
     */
    public function deleteTranslationUnit(int $id): void
    {
        $this->db->beginTransaction();
        try {
            // Delete associated translation versions first
            $stmt = $this->db->prepare(
                "DELETE FROM translation_versions WHERE translation_unit_id = ?"
            );
            $stmt->execute([$id]);

            // Then delete the translation unit
            $stmt = $this->db->prepare(
                "DELETE FROM translation_units WHERE id = ?"
            );
            $stmt->execute([$id]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
