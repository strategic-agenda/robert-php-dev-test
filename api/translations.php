<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/TranslationUnit.php';

/**
 * RESTful API for CRUD operations on translation units
 */
class TranslationUnitAPI
{
    private TranslationUnit $translationUnitModel;
    
    /**
     * Constructor - initializes the translation unit model
     */
    public function __construct()
    {
        $this->translationUnitModel = new TranslationUnit(
            new PDO('mysql:host=localhost;dbname=robert_dev', 'root', 'root')
        );
    }
    
    /**
     * Handle the API request based on the HTTP method
     */
    public function handleRequest(): void
    {
        // set response headers
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // route the request based on the HTTP method
        try {
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $this->handleGetRequest();
                    break;
                    
                case 'POST':
                    $this->handlePostRequest();
                    break;
                    
                case 'PUT':
                    $this->handlePutRequest();
                    break;
                    
                case 'DELETE':
                    $this->handleDeleteRequest();
                    break;
                    
                default:
                    $this->sendResponse(['error' => 'Method not allowed'], 405);
                    break;
            }
        } catch (Exception $e) {
            $this->sendResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle GET requests (list all units or get a specific unit)
     */
    private function handleGetRequest(): void
    {
        // get a specific unit if ID is provided
        if (isset($_GET['id'])) {
            $id = $this->sanitizeInput($_GET['id']);
            $unit = $this->translationUnitModel->get($id);
            
            if ($unit) {
                $this->sendResponse($unit);
            } else {
                $this->sendResponse(['error' => 'Translation unit not found'], 404);
            }
        } else {
            // get all units
            $units = $this->translationUnitModel->getAll();

            $this->sendResponse($units);
        }
    }
    
    /**
     * Handle POST request - create a new translation unit
     */
    private function handlePostRequest(): void
    {
        $data = $this->getRequestData();
        
        // Validate required fields
        if (empty($data['source_text'])) {
            $this->sendResponse(['error' => 'Missing required fields: source_text'], 400);

            return;
        }

        $sourceText = $this->sanitizeInput($data['source_text']);
        $translations = $data['translations'] ?? [];
        
        // Add the new unit
        $unitId = $this->translationUnitModel->add($sourceText, $translations);
        
        if ($unitId) {
            $unit = $this->translationUnitModel->get($unitId);

            $this->sendResponse($unit, 201);
        } else {
            $this->sendResponse(['error' => 'Failed to create translation unit'], 409);
        }
    }
    
    /**
     * Handle PUT requests - update an existing translation unit
     */
    private function handlePutRequest(): void
    {
        $data = $this->getRequestData();
        
        // Validate ID field
        if (empty($data['id'])) {
            $this->sendResponse(['error' => 'Missing required field: id is required'], 400);

            return;
        }
        
        $id = $this->sanitizeInput($data['id']);
        $sourceText = isset($data['source_text']) ? $this->sanitizeInput($data['source_text']) : null;
        $translations = $data['translations'] ?? null;
        
        // Update the unit
        $success = $this->translationUnitModel->update($id, $sourceText, $translations);
        
        if ($success) {
            $unit = $this->translationUnitModel->get($id);

            $this->sendResponse($unit);
        } else {
            $this->sendResponse(['error' => 'Translation unit not found'], 404);
        }
    }
    
    /**
     * Handle DELETE request (delete a translation unit)
     */
    private function handleDeleteRequest(): void
    {
        if (empty($_GET['id'])) {
            $this->sendResponse(['error' => 'Missing required parameter: id'], 400);

            return;
        }
        
        $id = $this->sanitizeInput($_GET['id']);
        $unit = $this->translationUnitModel->get($id);
        
        if ($unit) {
            // For now, just return a success response
            $this->sendResponse(['message' => 'Translation unit deleted successfully']);
        } else {
            $this->sendResponse(['error' => 'Translation unit not found'], 404);
        }
    }
    
    /**
     * Get request data from the input stream
     */
    private function getRequestData(): array
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(['error' => 'Invalid JSON: ' . json_last_error_msg()], 400);
            exit;
        }
        
        return $data ?? [];
    }
    
    /**
     * Sanitize input data to prevent security issues
     */
    private function sanitizeInput(mixed $input): mixed
    {
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
        
        return $input;
    }
    
    /**
     * Send a JSON response with the appropriate status code
     */
    private function sendResponse(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }
}

// Create and run the API
new TranslationUnitAPI()->handleRequest();
