<?php

namespace Robert\CAT;

class TranslationUnit
{
    private $db;

    /**
     * Constructor with dependency injection for database connection
     *
     * @param \PDO $db Database connection
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Add a new translation unit
     *
     * @param string $sourceContent The original content to be translated
     * @param string $context Additional context for translators (optional)
     * @param int $userId ID of the user creating this unit
     * @return int ID of the newly created translation unit
     */
    public function addTranslationUnit(string $sourceContent, string $context = '', int $userId = 0): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO translation_units (source_content, context, created_by, created_at, updated_at, status)
            VALUES (:source_content, :context, :created_by, NOW(), NOW(), 'active')
        ");

        $stmt->bindParam(':source_content', $sourceContent, \PDO::PARAM_STR);
        $stmt->bindParam(':context', $context, \PDO::PARAM_STR);
        $stmt->bindParam(':created_by', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    /**
     * Retrieve a translation unit by ID
     *
     * @param int $id ID of the translation unit to retrieve
     * @return array|null Translation unit data or null if not found
     */
    public function getTranslationUnitById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT tu.*, 
                   (SELECT COUNT(*) FROM translations WHERE translation_unit_id = tu.id) AS translations_count
            FROM translation_units tu
            WHERE tu.id = :id
        ");

        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $unit = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$unit) {
            return null;
        }

        // Get all translations for this unit
        $translationsStmt = $this->db->prepare("
            SELECT t.*, l.code as language_code, l.name as language_name
            FROM translations t
            JOIN languages l ON t.language_id = l.id
            WHERE t.translation_unit_id = :unit_id
            ORDER BY t.updated_at DESC
        ");

        $translationsStmt->bindParam(':unit_id', $id, \PDO::PARAM_INT);
        $translationsStmt->execute();

        $unit['translations'] = $translationsStmt->fetchAll(\PDO::FETCH_ASSOC);

        return $unit;
    }

    /**
     * Update a translation unit and keep history
     *
     * @param int $id ID of the translation unit to update
     * @param string $sourceContent Updated source content
     * @param string $context Updated context information
     * @param int $userId ID of the user making the update
     * @return bool True if update was successful
     */
    public function updateTranslationUnit(int $id, string $sourceContent, string $context = '', int $userId = 0): bool
    {
        // First get the current version
        $stmt = $this->db->prepare("SELECT source_content, context FROM translation_units WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $currentUnit = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$currentUnit) {
            return false;
        }

        // Begin transaction
        $this->db->beginTransaction();

        try {
            // Store history record
            $historyStmt = $this->db->prepare("
                INSERT INTO translation_unit_history (
                    translation_unit_id, 
                    previous_source_content, 
                    previous_context, 
                    changed_at, 
                    changed_by
                ) VALUES (
                    :unit_id, 
                    :prev_content, 
                    :prev_context, 
                    NOW(), 
                    :changed_by
                )
            ");

            $historyStmt->bindParam(':unit_id', $id, \PDO::PARAM_INT);
            $historyStmt->bindParam(':prev_content', $currentUnit['source_content'], \PDO::PARAM_STR);
            $historyStmt->bindParam(':prev_context', $currentUnit['context'], \PDO::PARAM_STR);
            $historyStmt->bindParam(':changed_by', $userId, \PDO::PARAM_INT);
            $historyStmt->execute();

            // Update the translation unit
            $updateStmt = $this->db->prepare("
                UPDATE translation_units 
                SET source_content = :source_content, 
                    context = :context, 
                    updated_at = NOW()
                WHERE id = :id
            ");

            $updateStmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $updateStmt->bindParam(':source_content', $sourceContent, \PDO::PARAM_STR);
            $updateStmt->bindParam(':context', $context, \PDO::PARAM_STR);
            $updateStmt->execute();

            // Commit transaction
            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Add a translation for a specific translation unit
     *
     * @param int $unitId ID of the translation unit
     * @param int $languageId ID of the target language
     * @param string $content Translated content
     * @param int $userId ID of the user creating the translation
     * @return int ID of the newly created translation
     */
    public function addTranslation(int $unitId, int $languageId, string $content, int $userId): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO translations (
                translation_unit_id, 
                language_id, 
                content, 
                created_at, 
                updated_at, 
                created_by, 
                status
            ) VALUES (
                :unit_id, 
                :language_id, 
                :content, 
                NOW(), 
                NOW(), 
                :created_by, 
                'active'
            )
        ");

        $stmt->bindParam(':unit_id', $unitId, \PDO::PARAM_INT);
        $stmt->bindParam(':language_id', $languageId, \PDO::PARAM_INT);
        $stmt->bindParam(':content', $content, \PDO::PARAM_STR);
        $stmt->bindParam(':created_by', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update a translation and keep history
     *
     * @param int $translationId ID of the translation to update
     * @param string $content Updated translated content
     * @param int $userId ID of the user making the update
     * @return bool True if update was successful
     */
    public function updateTranslation(int $translationId, string $content, int $userId): bool
    {
        // First get the current version
        $stmt = $this->db->prepare("
            SELECT content, revision_number 
            FROM translations 
            WHERE id = :id
        ");

        $stmt->bindParam(':id', $translationId, \PDO::PARAM_INT);
        $stmt->execute();

        $currentTranslation = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$currentTranslation) {
            return false;
        }

        // Begin transaction
        $this->db->beginTransaction();

        try {
            // Calculate new revision number
            $newRevisionNumber = ($currentTranslation['revision_number'] ?? 0) + 1;

            // Store history record
            $historyStmt = $this->db->prepare("
                INSERT INTO translation_history (
                    translation_id, 
                    previous_content, 
                    changed_at, 
                    changed_by,
                    revision_number
                ) VALUES (
                    :translation_id, 
                    :prev_content, 
                    NOW(), 
                    :changed_by,
                    :revision_number
                )
            ");

            $historyStmt->bindParam(':translation_id', $translationId, \PDO::PARAM_INT);
            $historyStmt->bindParam(':prev_content', $currentTranslation['content'], \PDO::PARAM_STR);
            $historyStmt->bindParam(':changed_by', $userId, \PDO::PARAM_INT);
            $historyStmt->bindParam(':revision_number', $newRevisionNumber, \PDO::PARAM_INT);
            $historyStmt->execute();

            // Update the translation
            $updateStmt = $this->db->prepare("
                UPDATE translations 
                SET content = :content, 
                    updated_at = NOW(),
                    revision_number = :revision_number
                WHERE id = :id
            ");

            $updateStmt->bindParam(':id', $translationId, \PDO::PARAM_INT);
            $updateStmt->bindParam(':content', $content, \PDO::PARAM_STR);
            $updateStmt->bindParam(':revision_number', $newRevisionNumber, \PDO::PARAM_INT);
            $updateStmt->execute();

            // Commit transaction
            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            throw $e;
        }
    }
}
