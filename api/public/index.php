<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) {
            continue; // Skip comments
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        putenv("$key=$value");
    }
}

// Initialize the dependency container
$container = \App\Config\DependencyContainer::build();

// Extract route from URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// API endpoint prefix
$apiRoute = array_search('api', $uri);
if ($apiRoute === false) {
    sendErrorResponse('Invalid API endpoint', 404);
}

// Extract route parts after /api/
$routeParts = array_slice($uri, $apiRoute + 1);
$resource = $routeParts[0] ?? null;
$id = $routeParts[1] ?? null;
$action = $routeParts[2] ?? null;

// API Routing
try {
    switch ($resource) {
        case 'units':
            $controller = $container->get(\App\Controllers\TranslationUnitController::class);
            $controller->handleRequest($_SERVER['REQUEST_METHOD'], $id, $action);
            break;
        
        case 'languages':
            $controller = $container->get(\App\Controllers\LanguageController::class);
            $controller->handleRequest($_SERVER['REQUEST_METHOD'], $id, $action);
            break;
            
        default:
            sendErrorResponse('Resource not found', 404);
    }
} catch (Exception $e) {
    $statusCode = ($e instanceof \InvalidArgumentException) ? 400 : 500;
    sendErrorResponse($e->getMessage(), $statusCode);
}

/**
 * Send an error response
 * 
 * @param string $message
 * @param int $statusCode
 * @return void
 */
function sendErrorResponse(string $message, int $statusCode): void
{
    http_response_code($statusCode);
    echo json_encode([
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
    ], JSON_PRETTY_PRINT);
    exit;
} 