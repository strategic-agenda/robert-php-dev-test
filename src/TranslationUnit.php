<?php

namespace App;

use PDO;

class TranslationUnit
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function addUnit(string $id, string $source, array $translations = []): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO translation_units (id, source, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ");
            $stmt->execute([$id, $source]);

            if (!empty($translations)) {
                $stmt = $this->db->prepare("
                    INSERT INTO translations (unit_id, lang_code, text)
                    VALUES (?, ?, ?)
                ");
                foreach ($translations as $lang => $text) {
                    $stmt->execute([$id, $lang, $text]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getUnit(string $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT tu.*, GROUP_CONCAT(CONCAT(t.lang_code, ':', t.text)) as translations
            FROM translation_units tu
            LEFT JOIN translations t ON tu.id = t.unit_id
            WHERE tu.id = ?
            GROUP BY tu.id
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        $translations = [];
        if ($result['translations']) {
            foreach (explode(',', $result['translations']) as $trans) {
                list($lang, $text) = explode(':', $trans, 2);
                $translations[$lang] = $text;
            }
        }

        return [
            'id' => $result['id'],
            'source' => $result['source'],
            'translations' => $translations,
            'created_at' => $result['created_at'],
            'updated_at' => $result['updated_at']
        ];
    }

    public function updateTranslation(string $id, string $lang, string $newText): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT text FROM translations WHERE unit_id = ? AND lang_code = ?");
            $stmt->execute([$id, $lang]);
            $oldText = $stmt->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO translations (unit_id, lang_code, text)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE text = ?
            ");
            $stmt->execute([$id, $lang, $newText, $newText]);

            $stmt = $this->db->prepare("UPDATE translation_units SET updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);

            if ($oldText !== $newText) {
                $stmt = $this->db->prepare("
                    INSERT INTO translation_history (unit_id, lang_code, old_text, new_text, timestamp)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$id, $lang, $oldText, $newText]);
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getHistory(string $id): array
    {
        $stmt = $this->db->prepare("
            SELECT lang_code as lang, old_text as old, new_text as new, timestamp
            FROM translation_history
            WHERE unit_id = ?
            ORDER BY timestamp DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUnit(string $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM translation_units WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function listUnits(): array
    {
        $stmt = $this->db->query("
            SELECT tu.*, GROUP_CONCAT(CONCAT(t.lang_code, ':', t.text)) as translations
            FROM translation_units tu
            LEFT JOIN translations t ON tu.id = t.unit_id
            GROUP BY tu.id
        ");
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $units = [];

        foreach ($results as $result) {
            $translations = [];
            if ($result['translations']) {
                foreach (explode(',', $result['translations']) as $trans) {
                    list($lang, $text) = explode(':', $trans, 2);
                    $translations[$lang] = $text;
                }
            }

            $units[] = [
                'id' => $result['id'],
                'source' => $result['source'],
                'translations' => $translations,
                'created_at' => $result['created_at'],
                'updated_at' => $result['updated_at']
            ];
        }

        return $units;
    }
}
