<?php

require_once __DIR__ . '/BaseApiController.php';
require_once __DIR__ . '/../src/Language.php';

use Robert\CAT\Language;

/**
 * LanguagesController - Handles API requests for languages
 *
 * Refactored to use the Language model for business logic
 */
class LanguagesController extends BaseApiController
{
    /**
     * @var Language
     */
    private Language $languageModel;

    /**
     * Define the regex pattern to extract language ID from URI
     */
    protected function getResourcePattern(): string
    {
        return '/\/api\/languages\/(\d+)/';
    }

    /**
     * Initialize the Language model
     * This method is called from handler methods before processing any request
     */
    private function initModel(): void
    {
        $this->languageModel = new Language($this->db);
    }

    /**
     * Handle GET requests for languages
     */
    protected function handleGet(): void
    {
        $this->initModel();

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
        $language = $this->languageModel->getLanguageById($id);

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

        // Add filter for enabled languages if requested
        $enabled = isset($_GET['enabled']) ? filter_var($_GET['enabled'], FILTER_VALIDATE_BOOLEAN) : null;

        // Get pagination parameters
        $pagination = getPaginationParams($_GET, 25);

        try {
            // Get total count
            $totalCount = $this->languageModel->countLanguages($search, $enabled);

            // Get language items for current page
            $languages = $this->languageModel->getLanguages($search, $enabled);

            // Build response with pagination info
            $result = [
                'data' => $languages,
                'pagination' => formatPaginationInfo($pagination['page'], $pagination['perPage'], $totalCount)
            ];

            sendJsonResponse($result, 200, $this->db);
        } catch (Exception $e) {
            sendJsonResponse(['error' => 'Failed to fetch languages: ' . $e->getMessage()], 500, $this->db);
        }
    }

    /**
     * Handle POST requests for languages
     */
    protected function handlePost(): void
    {
        $this->initModel();

        // Validate required fields
        if (empty($this->requestBody['name']) || empty($this->requestBody['code'])) {
            sendJsonResponse(['error' => 'Name and code are required'], 400, $this->db);
            return;
        }

        // Check if language code already exists
        if ($this->languageModel->languageCodeExists($this->requestBody['code'])) {
            sendJsonResponse(['error' => 'Language with this code already exists'], 409, $this->db);
            return;
        }

        // Set default values for optional fields
        $isRtl = isset($this->requestBody['is_rtl']) && (bool)$this->requestBody['is_rtl'];
        $enabled = !isset($this->requestBody['enabled']) || (bool)$this->requestBody['enabled'];

        try {
            // Add the new language
            $languageId = $this->languageModel->addLanguage(
                $this->requestBody['code'],
                $this->requestBody['name'],
                $isRtl,
                $enabled
            );

            sendJsonResponse([
                'message' => 'Language created successfully',
                'id' => $languageId
            ], 201, $this->db);
        } catch (Exception $e) {
            sendJsonResponse(['error' => 'Failed to create language: ' . $e->getMessage()], 500, $this->db);
        }
    }

    /**
     * Handle PUT requests for languages
     */
    protected function handlePut(): void
    {
        $this->initModel();

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
            if ($this->languageModel->languageCodeExists($this->requestBody['code'], $this->resourceId)) {
                sendJsonResponse(['error' => 'Another language with this code already exists'], 409, $this->db);
                return;
            }
        }

        try {
            // Update the language
            $success = $this->languageModel->updateLanguage(
                $this->resourceId,
                $this->requestBody
            );

            if (!$success) {
                sendJsonResponse(['error' => 'Failed to update language'], 500, $this->db);
                return;
            }

            sendJsonResponse(['message' => 'Language updated successfully'], 200, $this->db);
        } catch (Exception $e) {
            sendJsonResponse(['error' => 'Failed to update language: ' . $e->getMessage()], 500, $this->db);
        }
    }

    /**
     * Handle DELETE requests for languages
     */
    protected function handleDelete(): void
    {
        $this->initModel();

        // Check if the language exists
        if (!$this->resourceExists($this->resourceId, 'languages')) {
            sendJsonResponse(['error' => 'Language not found'], 404, $this->db);
            return;
        }

        try {
            // Check if the language is in use (has translations)
            $translationsCount = $this->languageModel->countTranslations($this->resourceId);

            if ($translationsCount > 0) {
                // Soft delete (disable the language) if it has translations
                $success = $this->languageModel->disableLanguage($this->resourceId);

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
                $success = $this->languageModel->deleteLanguage($this->resourceId);

                if (!$success) {
                    sendJsonResponse(['error' => 'Failed to delete language'], 500, $this->db);
                    return;
                }

                sendJsonResponse(['message' => 'Language deleted successfully'], 200, $this->db);
            }
        } catch (Exception $e) {
            sendJsonResponse(['error' => 'Failed to delete language: ' . $e->getMessage()], 500, $this->db);
        }
    }
}
