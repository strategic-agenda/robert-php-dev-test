<?php
/**
 * API Utilities
 *
 * Common functions and utilities shared across all API endpoints
 */

/**
 * Setup CORS headers and handle preflight requests
 */
function setupApiHeaders(): void
{
    // --- CORS Headers - Set these FIRST ---
    // Allow requests from your React app's origin (or '*' for development)
    header("Access-Control-Allow-Origin: *");

    // Allowed HTTP methods
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");

    // Allowed HTTP headers requested by the client
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN");

    // Allow credentials (like cookies or authorization headers) if needed
    header("Access-Control-Allow-Credentials: true");

    // Set maximum age for preflight requests cache (in seconds)
    header("Access-Control-Max-Age: 86400"); // 1 day

    // --- Handle HTTP OPTIONS method (preflight request) ---
    // This must come *after* setting the Allow headers above
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        // Just send acknowledge headers and exit.
        // No further processing needed for OPTIONS requests.
        http_response_code(204);
        exit();
    }

    // --- Set Content-Type for actual API responses (after handling OPTIONS) ---
    header("Content-Type: application/json; charset=UTF-8");
}

/**
 * Parse request URI to extract resource ID if present
 *
 * @param string $pattern Regex pattern to match (e.g., '/\/api\/languages\/(\d+)/')
 * @return int|null The extracted ID or null if not found
 */
function getResourceIdFromUri(string $pattern): ?int
{
    $requestUri = $_SERVER['REQUEST_URI'];
    $id = null;

    if (preg_match($pattern, $requestUri, $matches)) {
        $id = (int) $matches[1];
    }

    return $id;
}

/**
 * Parse JSON body for POST and PUT requests
 *
 * @return array|null Parsed JSON data or null if not applicable
 */
function parseJsonBody(): ?array
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
        $jsonBody = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJsonResponse(['error' => 'Invalid JSON provided'], 400);
            exit;
        }

        return $jsonBody;
    }

    return null;
}


/**
 * Get database connection using the helper function from bootstrap
 *
 * @return PDO Database connection
 */
function getDbConnection(): PDO
{
    try {
        return db_connect();
    } catch (PDOException $e) {
        sendJsonResponse(['error' => 'Database connection failed: ' . $e->getMessage()], 500);
        exit;
    }
}

/**
 * Close database connection
 *
 * @param PDO|null $connection The PDO connection to close
 * @return void
 */
function closeDbConnection(?PDO &$connection): void
{
    if ($connection instanceof PDO) {
        $connection = null;
    }
}

/**
 * Handle errors in a consistent way across all API endpoints
 *
 * @param Exception $e The exception that was thrown
 */
function handleApiError(Exception $e): void
{
    // Enhanced error handling based on environment
    if (defined('APP_DEBUG') && APP_DEBUG) {
        sendJsonResponse([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    } else {
        sendJsonResponse(['error' => 'Internal server error'], 500);
    }
}

/**
 * Send JSON response with appropriate status code
 *
 * @param mixed $data Response data
 * @param int $statusCode HTTP status code
 * @param PDO|null $dbConnection Optional database connection to close before exit
 */
function sendJsonResponse($data, int $statusCode = 200, ?PDO &$dbConnection = null): void
{
    http_response_code($statusCode);

    // Close database connection if provided
    closeDbConnection($dbConnection);

    // Output the response
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Handle pagination parameters
 *
 * @param array $queryParams Usually $_GET
 * @param int $defaultPerPage Default number of items per page
 * @return array Array with 'page', 'perPage', and 'offset' values
 */
function getPaginationParams(array $queryParams, int $defaultPerPage = 10): array
{
    $page = (int)($queryParams['page'] ?? 1);
    $perPage = (int)($queryParams['per_page'] ?? $defaultPerPage);
    $offset = ($page - 1) * $perPage;

    return [
        'page' => $page,
        'perPage' => $perPage,
        'offset' => $offset
    ];
}

/**
 * Format pagination information for response
 *
 * @param int $page Current page number
 * @param int $perPage Items per page
 * @param int $total Total number of items
 * @return array Formatted pagination information
 */
function formatPaginationInfo(int $page, int $perPage, int $total): array
{
    return [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => ceil($total / $perPage)
    ];
}

/**
 * Get current authenticated user ID
 * In a real app, this would come from authentication
 *
 * @return int The user ID
 */
function getCurrentUserId(): int
{
    // Hardcoded for demo purposes
    return 1;
}
