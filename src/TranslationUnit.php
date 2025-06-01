<?php

class TranslationUnit
{
    // Implement the class as per the tasks.
    private $pdo;

    public function __construct($dbPath = __DIR__ . '/../database/database.sqlite')
    {
        if (!file_exists(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0755, true);
        }

        $this->pdo = new PDO('sqlite:' . $dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->initializeSchema();
    }

    private function initializeSchema()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS documents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                source_language TEXT,
                target_language TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS translation_units (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                document_id INTEGER,
                unit_index INTEGER,
                source_text TEXT,
                target_text TEXT,
                version INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (document_id) REFERENCES documents(id)
            );
        ");

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS translation_unit_versions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                translation_unit_id INTEGER,
                target_text TEXT,
                version INTEGER,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                translated_by TEXT,
                notes TEXT,
                FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id)
            );
        ");
    }

    public function createDocument($name, $sourceLang, $targetLang)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO documents (name, source_language, target_language)
            VALUES (:name, :source_language, :target_language)
        ");
        $stmt->execute([
            ':name' => $name,
            ':source_language' => $sourceLang,
            ':target_language' => $targetLang
        ]);
        return $this->pdo->lastInsertId();
    }

    public function addTranslationUnit($documentId, $unitIndex, $sourceText, $targetText = null)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO translation_units (document_id, unit_index, source_text, target_text)
            VALUES (:document_id, :unit_index, :source_text, :target_text)
        ");
        $stmt->execute([
            ':document_id' => $documentId,
            ':unit_index' => $unitIndex,
            ':source_text' => $sourceText,
            ':target_text' => $targetText
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getTranslationUnitById($unitId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM translation_units WHERE id = :id");
        $stmt->execute([':id' => $unitId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateTranslationUnit($unitId, $newTargetText, $translatedBy = null, $notes = null)
    {
            // Begin transaction
            $this->pdo->beginTransaction();

            try {
                // Fetch current translation unit
                $stmt = $this->pdo->prepare("SELECT * FROM translation_units WHERE id = :id");
                $stmt->execute([':id' => $unitId]);
                $unit = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$unit) {
                    throw new Exception("Translation unit not found.");
                }

                // Increment version
                $newVersion = $unit['version'] + 1;

                // Update translation_units table
                $updateStmt = $this->pdo->prepare("
                    UPDATE translation_units
                    SET target_text = :target_text,
                        version = :version,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':target_text' => $newTargetText,
                    ':version' => $newVersion,
                    ':id' => $unitId
                ]);

                // Insert into translation_unit_versions table
                $insertStmt = $this->pdo->prepare("
                    INSERT INTO translation_unit_versions (
                        translation_unit_id,
                        target_text,
                        version,
                        updated_at,
                        translated_by,
                        notes
                    ) VALUES (
                        :translation_unit_id,
                        :target_text,
                        :version,
                        CURRENT_TIMESTAMP,
                        :translated_by,
                        :notes
                    )
                ");
                $insertStmt->execute([
                    ':translation_unit_id' => $unitId,
                    ':target_text' => $newTargetText,
                    ':version' => $newVersion,
                    ':translated_by' => $translatedBy,
                    ':notes' => $notes
                ]);

                // Commit transaction
                $this->pdo->commit();
            } catch (Exception $e) {
                // Rollback transaction on error
                $this->pdo->rollBack();
                throw $e;
            }
    }

    public function deleteTranslationUnit($unitId)
    {
        $stmt = $this->pdo->prepare("DELETE FROM translation_units WHERE id = :id");
        return $stmt->execute([':id' => $unitId]);
    }
}
