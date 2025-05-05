<?php
// Router file for the PHP development server

$requestUri = $_SERVER['REQUEST_URI'];

// Route all translations API requests to translations.php
if (strpos($requestUri, '/api/translations') === 0) {
    require_once __DIR__ . '/translations.php';
    exit;
}

// Add other API routes here

// If no routes match, return 404
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => [
        'message' => 'Endpoint not found',
        'code' => 404
    ]
]);
exit;
