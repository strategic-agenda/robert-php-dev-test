<?php

require_once '../vendor/autoload.php';

use Robert\CAT\Factory\TranslationUnitFactory;
use Robert\CAT\Repository\TranslationUnitRepository;

// Database configuration
$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'dbname' => getenv('DB_NAME') ?: 'robert_cat',
    'user' => getenv('DB_USER') ?: 'robert_user',
    'password' => getenv('DB_PASSWORD') ?: 'R0b3rt_P@ssword!'
];

// Initialize database connection
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    respondWithError('Database connection failed: ' . $e->getMessage(), 500);
}

// Initialize repository and factory
$repository = new TranslationUnitRepository($pdo);
$factory = new TranslationUnitFactory();

// Set headers for JSON API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/api/translations';
$path = parse_url($requestUri, PHP_URL_PATH);

// Extract path parameters
$pathParams = [];
if (strpos($path, $basePath) === 0) {
    $pathPart = substr($path, strlen($basePath));
    $pathPart = trim($pathPart, '/');
    if (!empty($pathPart)) {
        $pathParams = explode('/', $pathPart);
    }
}

// Extract query parameters
$queryParams = $_GET;

// Parse JSON request body for POST and PUT requests
$requestBody = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $requestBody = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        respondWithError('Invalid JSON in request body', 400);
    }
}

// Route the request
try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetRequest($pathParams, $queryParams, $repository, $factory);
            break;

        case 'POST':
            handlePostRequest($pathParams, $requestBody, $repository, $factory);
            break;

        case 'PUT':
            handlePutRequest($pathParams, $requestBody, $repository, $factory);
            break;

        case 'DELETE':
            handleDeleteRequest($pathParams, $repository);
            break;

        default:
            respondWithError('Method not allowed', 405);
    }
} catch (Exception $e) {
    respondWithError('Internal server error: ' . $e->getMessage(), 500);
}

/**
 * Handle GET requests
 */
function handleGetRequest(array $pathParams, array $queryParams, TranslationUnitRepository $repository, TranslationUnitFactory $factory): void
{
    // GET /api/translations - List all units (with pagination)
    if (empty($pathParams)) {
        $page = isset($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $limit = isset($queryParams['limit']) ? (int)$queryParams['limit'] : 10;
        $documentId = isset($queryParams['document_id']) ? (int)$queryParams['document_id'] : null;

        if ($documentId) {
            $units = $repository->findByDocumentId($documentId);
            // Simple pagination
            $totalUnits = count($units);
            $offset = ($page - 1) * $limit;
            $units = array_slice($units, $offset, $limit);

            respondWithSuccess([
                'units' => array_map(function ($unit) {
                    return $unit->toArray();
                }, $units),
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $totalUnits,
                    'pages' => ceil($totalUnits / $limit)
                ]
            ]);
        } else {
            respondWithError('Document ID is required', 400);
        }
        return;
    }

    // GET /api/translations/{id} - Get a specific unit
    if (count($pathParams) === 1 && is_numeric($pathParams[0])) {
        $id = (int)$pathParams[0];
        $unit = $repository->findById($id);

        if ($unit) {
            respondWithSuccess($unit->toArray());
        } else {
            respondWithError('Translation unit not found', 404);
        }
        return;
    }

    // GET /api/translations/{id}/history - Get history for a unit
    if (count($pathParams) === 2 && is_numeric($pathParams[0]) && $pathParams[1] === 'history') {
        $id = (int)$pathParams[0];
        $unit = $repository->findById($id);

        if ($unit) {
            respondWithSuccess(['history' => $unit->getHistory()]);
        } else {
            respondWithError('Translation unit not found', 404);
        }
        return;
    }

    respondWithError('Endpoint not found', 404);
}

/**
 * Handle POST requests
 */
function handlePostRequest(array $pathParams, ?array $requestBody, TranslationUnitRepository $repository, TranslationUnitFactory $factory): void
{
    // POST /api/translations - Create a new unit
    if (empty($pathParams)) {
        if (!$requestBody || !validateUnitData($requestBody)) {
            respondWithError('Invalid translation unit data', 400);
            return;
        }

        $unit = $factory->createFromArray($requestBody);
        $repository->save($unit);

        respondWithSuccess($unit->toArray(), 201);
        return;
    }

    // POST /api/translations/{id}/translate - Add a translation
    if (count($pathParams) === 2 && is_numeric($pathParams[0]) && $pathParams[1] === 'translate') {
        $id = (int)$pathParams[0];
        $unit = $repository->findById($id);

        if (!$unit) {
            respondWithError('Translation unit not found', 404);
            return;
        }

        if (!$requestBody || !isset($requestBody['language_id']) || !isset($requestBody['content']) || !isset($requestBody['translated_by'])) {
            respondWithError('Invalid translation data', 400);
            return;
        }

        $unit->addTranslation(
            (int)$requestBody['language_id'],
            $requestBody['content'],
            (int)$requestBody['translated_by']
        );

        $repository->save($unit);

        respondWithSuccess($unit->toArray());
        return;
    }

    respondWithError('Endpoint not found', 404);
}

/**
 * Handle PUT requests
 */
function handlePutRequest(array $pathParams, ?array $requestBody, TranslationUnitRepository $repository, TranslationUnitFactory $factory): void
{
    // PUT /api/translations/{id} - Update a unit
    if (count($pathParams) === 1 && is_numeric($pathParams[0])) {
        $id = (int)$pathParams[0];
        $unit = $repository->findById($id);

        if (!$unit) {
            respondWithError('Translation unit not found', 404);
            return;
        }

        if (!$requestBody) {
            respondWithError('Invalid request data', 400);
            return;
        }

        // Update source content if provided
        if (isset($requestBody['source_content'])) {
            $unit->setSourceContent($requestBody['source_content']);
        }

        // Update context if provided
        if (isset($requestBody['context'])) {
            $unit->setContext($requestBody['context']);
        }

        // Update translations if provided
        if (isset($requestBody['translations']) && is_array($requestBody['translations'])) {
            foreach ($requestBody['translations'] as $languageId => $translation) {
                if (isset($translation['content']) && isset($translation['translated_by'])) {
                    $unit->addTranslation(
                        (int)$languageId,
                        $translation['content'],
                        (int)$translation['translated_by']
                    );
                }

                if (isset($translation['status']) && isset($translation['reviewed_by'])) {
                    $unit->updateTranslationStatus(
                        (int)$languageId,
                        $translation['status'],
                        (int)$translation['reviewed_by']
                    );
                }
            }
        }

        $repository->save($unit);

        respondWithSuccess($unit->toArray());
        return;
    }

    // PUT /api/translations/{id}/status - Update translation status
    if (count($pathParams) === 2 && is_numeric($pathParams[0]) && $pathParams[1] === 'status') {
        $id = (int)$pathParams[0];
        $unit = $repository->findById($id);

        if (!$unit) {
            respondWithError('Translation unit not found', 404);
            return;
        }

        if (!$requestBody || !isset($requestBody['language_id']) || !isset($requestBody['status']) || !isset($requestBody['reviewed_by'])) {
            respondWithError('Invalid status update data', 400);
            return;
        }

        $unit->updateTranslationStatus(
            (int)$requestBody['language_id'],
            $requestBody['status'],
            (int)$requestBody['reviewed_by']
        );

        $repository->save($unit);

        respondWithSuccess($unit->toArray());
        return;
    }

    respondWithError('Endpoint not found', 404);
}

/**
 * Handle DELETE requests
 */
function handleDeleteRequest(array $pathParams, TranslationUnitRepository $repository): void
{
    // DELETE /api/translations/{id} - Delete a unit
    if (count($pathParams) === 1 && is_numeric($pathParams[0])) {
        $id = (int)$pathParams[0];
        $success = $repository->delete($id);

        if ($success) {
            respondWithSuccess(['message' => 'Translation unit deleted successfully']);
        } else {
            respondWithError('Translation unit not found or could not be deleted', 404);
        }
        return;
    }

    respondWithError('Endpoint not found', 404);
}

/**
 * Validate translation unit data
 */
function validateUnitData(array $data): bool
{
    return isset($data['document_id']) &&
        isset($data['sequence_number']) &&
        isset($data['source_content']);
}

/**
 * Send a success response
 */
function respondWithSuccess($data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit;
}

/**
 * Send an error response
 */
function respondWithError(string $message, int $statusCode = 400): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $message,
            'code' => $statusCode
        ]
    ]);
    exit;
}
