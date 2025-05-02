<?php

require_once __DIR__ . '/BaseApiController.php';

/**
 * LanguagesController - Handles API requests for languages
 */
class LanguagesController extends BaseApiController
{
    /**
     * Define the regex pattern to extract language ID from URI
     */
    protected function getResourcePattern(): string
    {
        return '/\/api\/languages\/(\d+)/';
    }

    /**
     * Handle GET requests for languages
     */
    protected function handleGet(): void
    {
        if ($this->resourceId) {
            $this->getLanguageById($this->resourceId);
        } else {
            $this->getLanguagesList();
        }
    }

    /**
     * Get a specific language by ID
     *
     * @param int $id Language ID
     */
    private function getLanguageById(int $id): void
    {
        $stmt = $this->db->prepare("SELECT * FROM languages WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $language = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$language) {
            sendJsonResponse(['error' => 'Language not found'], 404, $this->db);
            return;
        }

        sendJsonResponse($language, 200, $this->db);
    }

    /**
     * Get a paginated list of languages with optional filtering
     */
    private function getLanguagesList(): void
    {
        // Get search filter if provided
        $search = $_GET['search'] ?? '';
        $whereClause = '';
        $params = [];

        if (!empty($search)) {
            $searchFields = [
                'name' => $search,
                'code' => $search
            ];
            $whereClause = $this->buildSearchWhereClause($searchFields, $params, 'OR');
        }

        // Add filter for enabled languages if requested
        $enabled = isset($_GET['enabled']) ? filter_var($_GET['enabled'], FILTER_VALIDATE_BOOLEAN) : null;
        if ($enabled !== null) {
            $whereClause = empty($whereClause) ? "WHERE enabled = :enabled" : "$whereClause AND enabled = :enabled";
            $params[':enabled'] = $enabled;
        }

        $baseQuery = "
            SELECT l.*, 
                   (SELECT COUNT(*) FROM translations WHERE language_id = l.id) AS translations_count
            FROM languages l
            $whereClause
        ";

        $countQuery = "SELECT COUNT(*) FROM languages $whereClause";

        $result = $this->getPaginatedResults($baseQuery, $countQuery, $params, 'l.name ASC', 25);

        sendJsonResponse($result, 200, $this->db);
    }

    /**
     * Handle POST requests for languages
     */
    protected function handlePost(): void
    {
        // Validate required fields
        if (empty($this->requestBody['name']) || empty($this->requestBody['code'])) {
            sendJsonResponse(['error' => 'Name and code are required'], 400, $this->db);
            return;
        }

        // Check if language code already exists
        $checkStmt = $this->db->prepare("SELECT id FROM languages WHERE code = :code");
        $checkStmt->bindParam(':code', $this->requestBody['code'], PDO::PARAM_STR);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            sendJsonResponse(['error' => 'Language with this code already exists'], 409, $this->db);
            return;
        }

        // Prepare insert statement
        $stmt = $this->db->prepare(
            "
            INSERT INTO languages (code, name, is_rtl, enabled)
            VALUES (:code, :name, :is_rtl, :enabled)
        "
        );

        // Set default values for optional fields
        $isRtl = isset($this->requestBody['is_rtl']) && (bool)$this->requestBody['is_rtl'];
        $enabled = !isset($this->requestBody['enabled']) || (bool)$this->requestBody['enabled'];

        // Bind parameters
        $stmt->bindParam(':code', $this->requestBody['code'], PDO::PARAM_STR);
        $stmt->bindParam(':name', $this->requestBody['name'], PDO::PARAM_STR);
        $stmt->bindParam(':is_rtl', $isRtl, PDO::PARAM_BOOL);
        $stmt->bindParam(':enabled', $enabled, PDO::PARAM_BOOL);

        $success = $stmt->execute();

        if (!$success) {
            sendJsonResponse(['error' => 'Failed to create language'], 500, $this->db);
            return;
        }

        // Get the newly created language ID
        $languageId = $this->db->lastInsertId();

        sendJsonResponse([
            'message' => 'Language created successfully',
            'id' => $languageId
        ], 201, $this->db);
    }

    /**
     * Handle PUT requests for languages
     */
    protected function handlePut(): void
    {
        // Check if the language exists
        if (!$this->resourceExists($this->resourceId, 'languages')) {
            sendJsonResponse(['error' => 'Language not found'], 404, $this->db);
            return;
        }

        // Validate that at least one field is being updated
        if (empty($this->requestBody['name']) && empty($this->requestBody['code']) &&
            !isset($this->requestBody['is_rtl']) && !isset($this->requestBody['enabled'])) {
            sendJsonResponse(['error' => 'No fields to update provided'], 400, $this->db);
            return;
        }

        // Check if code is changed and already exists
        if (!empty($this->requestBody['code'])) {
            $codeCheckStmt = $this->db->prepare("SELECT id FROM languages WHERE code = :code AND id != :id");
            $codeCheckStmt->bindParam(':code', $this->requestBody['code'], PDO::PARAM_STR);
            $codeCheckStmt->bindParam(':id', $this->resourceId, PDO::PARAM_INT);
            $codeCheckStmt->execute();

            if ($codeCheckStmt->fetch()) {
                sendJsonResponse(['error' => 'Another language with this code already exists'], 409, $this->db);
                return;
            }
        }

        // Build update SQL dynamically based on provided fields
        $updateFields = [];
        $params = [];

        if (!empty($this->requestBody['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $this->requestBody['name'];
        }

        if (!empty($this->requestBody['code'])) {
            $updateFields[] = "code = :code";
            $params[':code'] = $this->requestBody['code'];
        }

        if (isset($this->requestBody['is_rtl'])) {
            $updateFields[] = "is_rtl = :is_rtl";
            $params[':is_rtl'] = (bool)$this->requestBody['is_rtl'];
        }

        if (isset($this->requestBody['enabled'])) {
            $updateFields[] = "enabled = :enabled";
            $params[':enabled'] = (bool)$this->requestBody['enabled'];
        }

        $params[':id'] = $this->resourceId;

        // Prepare and execute update statement
        $sql = "UPDATE languages SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $success = $stmt->execute();

        if (!$success) {
            sendJsonResponse(['error' => 'Failed to update language'], 500, $this->db);
            return;
        }

        sendJsonResponse(['message' => 'Language updated successfully'], 200, $this->db);
    }

    /**
     * Handle DELETE requests for languages
     */
    protected function handleDelete(): void
    {
        // Check if the language exists
        if (!$this->resourceExists($this->resourceId, 'languages')) {
            sendJsonResponse(['error' => 'Language not found'], 404, $this->db);
            return;
        }

        // Check if the language is in use (has translations)
        $checkUsageStmt = $this->db->prepare("SELECT COUNT(*) FROM translations WHERE language_id = :id");
        $checkUsageStmt->bindParam(':id', $this->resourceId, PDO::PARAM_INT);
        $checkUsageStmt->execute();

        $translationsCount = (int)$checkUsageStmt->fetchColumn();

        if ($translationsCount > 0) {
            // Soft delete (disable the language) if it has translations
            $stmt = $this->db->prepare(
                "
                UPDATE languages 
                SET enabled = FALSE
                WHERE id = :id
            "
            );
            $stmt->bindParam(':id', $this->resourceId, PDO::PARAM_INT);
            $success = $stmt->execute();

            if (!$success) {
                sendJsonResponse(['error' => 'Failed to disable language'], 500, $this->db);
                return;
            }

            sendJsonResponse([
                'message' => 'Language disabled successfully',
                'notes' => 'Language was disabled instead of deleted because it has associated translations'
            ], 200, $this->db);
        } else {
            // Hard delete if the language has no translations
            $stmt = $this->db->prepare("DELETE FROM languages WHERE id = :id");
            $stmt->bindParam(':id', $this->resourceId, PDO::PARAM_INT);
            $success = $stmt->execute();

            if (!$success) {
                sendJsonResponse(['error' => 'Failed to delete language'], 500, $this->db);
                return;
            }

            sendJsonResponse(['message' => 'Language deleted successfully'], 200, $this->db);
        }
    }
}
