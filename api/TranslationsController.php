<?php

require_once __DIR__ . '/BaseApiController.php';

/**
 * TranslationsController - Handles API requests for translation units and translations
 */
class TranslationsController extends BaseApiController
{
    /**
     * Define the regex pattern to extract translation unit ID from URI
     */
    protected function getResourcePattern(): string
    {
        return '/\/api\/translations\/(\d+)/';
    }

    /**
     * Handle GET requests for translation units
     */
    protected function handleGet(): void
    {
        if ($this->resourceId) {
            $this->getTranslationById($this->resourceId);
        } else {
            $this->getTranslationsList();
        }
    }

    /**
     * Get a specific translation unit by ID with its translations
     *
     * @param int $id Translation unit ID
     */
    private function getTranslationById(int $id): void
    {
        // Get the translation unit
        $unitQuery = "
            SELECT tu.*
            FROM translation_units tu
            WHERE tu.id = :id
        ";

        $unitStmt = $this->db->prepare($unitQuery);
        $unitStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $unitStmt->execute();

        $unit = $unitStmt->fetch(PDO::FETCH_ASSOC);

        if (!$unit) {
            sendJsonResponse(['error' => 'Translation unit not found'], 404, $this->db);
            return;
        }

        // Get associated translations
        $translationsQuery = "
            SELECT t.*, l.name as language_name, l.code as language_code
            FROM translations t
            JOIN languages l ON t.language_id = l.id
            WHERE t.translation_unit_id = :unit_id
            ORDER BY l.name ASC
        ";

        $translationsStmt = $this->db->prepare($translationsQuery);
        $translationsStmt->bindParam(':unit_id', $id, PDO::PARAM_INT);
        $translationsStmt->execute();

        $translations = $translationsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Combine unit and translations
        $result = $unit;
        $result['translations'] = $translations;

        sendJsonResponse($result, 200, $this->db);
    }

    /**
     * Get a paginated list of translation units with optional filtering
     */
    private function getTranslationsList(): void
    {
        // Get search filter if provided
        $search = !empty($_GET['search']) ? trim($_GET['search']) : '';
        $whereClause = '';
        $params = [];

        if (!empty($search)) {
            $searchFields = [
                'source_content' => $search,
                'context' => $search
            ];
            $whereClause = $this->buildSearchWhereClause($searchFields, $params, 'OR');
        }

        // Add filter for status if requested
        $statusFilter = !empty($_GET['status']) ? trim($_GET['status']) : null;
        if (!empty($statusFilter)) {
            $whereClause = empty($whereClause) ? "WHERE status = :status" : "$whereClause AND status = :status";
            $params[':status'] = $statusFilter;
        }

        $baseQuery = "
            SELECT tu.*, 
                   (SELECT COUNT(*) FROM translations WHERE translation_unit_id = tu.id) AS translations_count
            FROM translation_units tu
            $whereClause
        ";

        $countQuery = "SELECT COUNT(*) FROM translation_units $whereClause";

        $result = $this->getPaginatedResults($baseQuery, $countQuery, $params, 'tu.updated_at DESC', 10);

        sendJsonResponse($result, 200, $this->db);
    }

    /**
     * Handle POST requests for translation units or translations
     */
    protected function handlePost(): void
    {
        // Check if we're adding a translation unit or a translation
        if (isset($this->requestBody['source_content'])) {
            $this->addTranslationUnit();
        } elseif (isset($this->requestBody['translation_unit_id'], $this->requestBody['language_id'], $this->requestBody['content'])) {
            $this->addTranslation();
        } else {
            sendJsonResponse(['error' => 'Invalid request data'], 400, $this->db);
        }
    }

    /**
     * Add a new translation unit
     */
    private function addTranslationUnit(): void
    {
        // Validate required fields
        if (empty($this->requestBody['source_content'])) {
            sendJsonResponse(['error' => 'Source content is required'], 400, $this->db);
            return;
        }

        $context = $this->requestBody['context'] ?? '';

        // Insert the new translation unit
        $stmt = $this->db->prepare(
            "
            INSERT INTO translation_units 
                (source_content, context, status, created_by, created_at, updated_at) 
            VALUES 
                (:source_content, :context, 'active', :user_id, NOW(), NOW())
        "
        );

        $stmt->bindParam(':source_content', $this->requestBody['source_content'], PDO::PARAM_STR);
        $stmt->bindParam(':context', $context, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);

        $success = $stmt->execute();

        if (!$success) {
            sendJsonResponse(['error' => 'Failed to create translation unit'], 500, $this->db);
            return;
        }

        $unitId = $this->db->lastInsertId();

        sendJsonResponse([
            'message' => 'Translation unit created successfully',
            'id' => $unitId
        ], 201, $this->db);
    }

    /**
     * Add a new translation for an existing unit
     */
    private function addTranslation(): void
    {
        $unitId = (int)$this->requestBody['translation_unit_id'];
        $languageId = (int)$this->requestBody['language_id'];
        $content = $this->requestBody['content'];

        // Check if the translation unit exists
        if (!$this->resourceExists($unitId, 'translation_units')) {
            sendJsonResponse(['error' => 'Translation unit not found'], 404, $this->db);
            return;
        }

        // Check if the language exists
        if (!$this->resourceExists($languageId, 'languages')) {
            sendJsonResponse(['error' => 'Language not found'], 404, $this->db);
            return;
        }

        // Check if translation already exists
        $checkStmt = $this->db->prepare(
            "
            SELECT id FROM translations 
            WHERE translation_unit_id = :unit_id AND language_id = :language_id
        "
        );
        $checkStmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
        $checkStmt->bindParam(':language_id', $languageId, PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            sendJsonResponse(['error' => 'Translation already exists for this language'], 409, $this->db);
            return;
        }

        // Insert the new translation
        $stmt = $this->db->prepare(
            "
            INSERT INTO translations 
                (translation_unit_id, language_id, content, created_by, created_at, updated_at) 
            VALUES 
                (:unit_id, :language_id, :content, :user_id, NOW(), NOW())
        "
        );

        $stmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
        $stmt->bindParam(':language_id', $languageId, PDO::PARAM_INT);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);

        $success = $stmt->execute();

        if (!$success) {
            sendJsonResponse(['error' => 'Failed to create translation'], 500, $this->db);
            return;
        }

        $translationId = $this->db->lastInsertId();

        // Update the translation unit's updated_at timestamp
        $updateStmt = $this->db->prepare(
            "
            UPDATE translation_units 
            SET updated_at = NOW(), created_by = :user_id 
            WHERE id = :unit_id
        "
        );
        $updateStmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);
        $updateStmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
        $updateStmt->execute();

        sendJsonResponse([
            'message' => 'Translation added successfully',
            'id' => $translationId
        ], 201, $this->db);
    }

    /**
     * Handle PUT requests for translation units or translations
     */
    protected function handlePut(): void
    {
        // Check what we're updating (unit or translation)
        if (isset($this->requestBody['source_content'])) {
            $this->updateTranslationUnit();
        } elseif (isset($this->requestBody['translation_id'], $this->requestBody['content'])) {
            $this->updateTranslation();
        } else {
            sendJsonResponse(['error' => 'Invalid request data'], 400, $this->db);
        }
    }

    /**
     * Update an existing translation unit
     */
    private function updateTranslationUnit(): void
    {
        // Check if the unit exists
        if (!$this->resourceExists($this->resourceId, 'translation_units')) {
            sendJsonResponse(['error' => 'Translation unit not found'], 404, $this->db);
            return;
        }

        $context = $this->requestBody['context'] ?? '';

        // Update the translation unit
        $stmt = $this->db->prepare(
            "
            UPDATE translation_units 
            SET source_content = :source_content, 
                context = :context, 
                created_by = :user_id, 
                updated_at = NOW() 
            WHERE id = :id
        "
        );

        $stmt->bindParam(':source_content', $this->requestBody['source_content'], PDO::PARAM_STR);
        $stmt->bindParam(':context', $context, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->resourceId, PDO::PARAM_INT);

        $success = $stmt->execute();

        if (!$success) {
            sendJsonResponse(['error' => 'Failed to update translation unit'], 500, $this->db);
            return;
        }

        sendJsonResponse(['message' => 'Translation unit updated successfully'], 200, $this->db);
    }

    /**
     * Update an existing translation
     */
    private function updateTranslation(): void
    {
        $translationId = (int)$this->requestBody['translation_id'];
        $content = $this->requestBody['content'];

        // Check if the translation exists and get the unit ID
        $checkStmt = $this->db->prepare(
            "
            SELECT translation_unit_id FROM translations WHERE id = :id
        "
        );
        $checkStmt->bindParam(':id', $translationId, PDO::PARAM_INT);
        $checkStmt->execute();

        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            sendJsonResponse(['error' => 'Translation not found'], 404, $this->db);
            return;
        }

        $unitId = $result['translation_unit_id'];

        // Update the translation
        $stmt = $this->db->prepare(
            "
            UPDATE translations 
            SET content = :content, 
                created_by = :user_id, 
                updated_at = NOW() 
            WHERE id = :id
        "
        );

        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $translationId, PDO::PARAM_INT);

        $success = $stmt->execute();

        if (!$success) {
            sendJsonResponse(['error' => 'Failed to update translation'], 500, $this->db);
            return;
        }

        // Update the translation unit's updated_at timestamp
        $updateStmt = $this->db->prepare(
            "
            UPDATE translation_units 
            SET updated_at = NOW(), created_by = :user_id 
            WHERE id = :unit_id
        "
        );
        $updateStmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);
        $updateStmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
        $updateStmt->execute();

        sendJsonResponse(['message' => 'Translation updated successfully'], 200, $this->db);
    }

    /**
     * Handle DELETE requests for translation units
     */
    protected function handleDelete(): void
    {
        // Check if the translation unit exists
        if (!$this->resourceExists($this->resourceId, 'translation_units')) {
            sendJsonResponse(['error' => 'Translation unit not found'], 404, $this->db);
            return;
        }

        // Delete the translation unit (soft delete by changing status)
        $stmt = $this->db->prepare(
            "
            UPDATE translation_units 
            SET status = 'archived', updated_at = NOW(), created_by = :user_id 
            WHERE id = :id
        "
        );

        $stmt->bindParam(':user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->resourceId, PDO::PARAM_INT);
        $success = $stmt->execute();

        if (!$success) {
            sendJsonResponse(['error' => 'Failed to delete translation unit'], 500, $this->db);
            return;
        }

        sendJsonResponse(['message' => 'Translation unit archived successfully'], 200, $this->db);
    }
}
