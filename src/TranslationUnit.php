<?php
class TranslationUnit {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=cat", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Add a new translation unit
    public function findOrCreateUnit($source, $sourceLang) {
        $stmt = $this->pdo->prepare("SELECT * FROM translation_units WHERE source = ? AND source_language = ?");
        $stmt->execute([$source, $sourceLang]);
        $unit = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($unit) return $unit;

        $stmt = $this->pdo->prepare("INSERT INTO translation_units (source, source_language) VALUES (?, ?)");
        $stmt->execute([$source, $sourceLang]);

        return [
            'id' => $this->pdo->lastInsertId(),
            'source' => $source,
            'source_language' => $sourceLang
        ];
    }

    // Add translation to an existing unit
    public function addTranslationToUnit($unitId, $translated, $targetLang) {
        $stmt = $this->pdo->prepare("INSERT INTO translations (unit_id, target_language, translated) VALUES (?, ?, ?)");
        $stmt->execute([$unitId, $targetLang, $translated]);
        return $this->pdo->lastInsertId();
    }

    // Get all translation units with their translations
    public function getAllUnits() {
        $stmt = $this->pdo->query("
            SELECT tu.id as unit_id, tu.source, tu.source_language, tr.id as translation_id,
                   tr.target_language, tr.translated, tr.version
            FROM translation_units tu
            LEFT JOIN translations tr ON tu.id = tr.unit_id
            ORDER BY tu.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a specific translation unit by ID
    public function getUnitById($id) {
        $stmt = $this->pdo->prepare("
            SELECT tu.id as unit_id, tu.source, tu.source_language, tr.id as translation_id,
                   tr.target_language, tr.translated, tr.version
            FROM translation_units tu
            LEFT JOIN translations tr ON tu.id = tr.unit_id
            WHERE tu.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update the translation unit and its translation
    public function updateUnitAndTranslation($unitId, $translationId, $source, $sourceLang, $translated, $targetLang) {
        $stmt = $this->pdo->prepare("UPDATE translation_units SET source = ?, source_language = ? WHERE id = ?");
        $stmt->execute([$source, $sourceLang, $unitId]);

        $stmt = $this->pdo->prepare("SELECT * FROM translations WHERE id = ? AND unit_id = ?");
        $stmt->execute([$translationId, $unitId]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($current) {
            $stmt = $this->pdo->prepare("INSERT INTO translation_history (translation_id, translated, version) VALUES (?, ?, ?)");
            $stmt->execute([$translationId, $current['translated'], $current['version']]);

            $newVersion = $current['version'] + 1;
            $stmt = $this->pdo->prepare("UPDATE translations SET translated = ?, target_language = ?, version = ? WHERE id = ? AND unit_id = ?");
            return $stmt->execute([$translated, $targetLang, $newVersion, $translationId, $unitId]);
        }

        return false;
    }

    public function getTranslationHistory($translationId) {
        $stmt = $this->pdo->prepare("SELECT * FROM translation_history WHERE translation_id = ? ORDER BY version DESC");
        $stmt->execute([$translationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Delete a translation unit and all its translations
    public function deleteUnit($unitId) {
        $stmt = $this->pdo->prepare("DELETE FROM translation_units WHERE id = ?");
        return $stmt->execute([$unitId]);
    }
}
?>