<?php
/**
 * Main application router
 *
 * Routes all requests to appropriate handlers
 */

// Strip off any query string so we only match the path
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Check if request is for API
if (preg_match('/^\/api\//', $uri)) {
    // All API requests now go through the API router
    include __DIR__ . '/api/api_router.php';
} else {
    // For non-API routes, handle 404 response
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(404);
    echo json_encode(['error' => 'Route not found']);
}
