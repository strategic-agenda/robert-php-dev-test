<?php
/**
 * API Router
 *
 * Routes API requests to the appropriate controller
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/LanguagesController.php';
require_once __DIR__ . '/TranslationsController.php';

// Get the request path
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route to the appropriate controller based on the request path
if (preg_match('/^\/api\/languages(\/.*)?$/', $requestPath)) {
    // Languages endpoint
    new LanguagesController();
} elseif (preg_match('/^\/api\/translations(\/.*)?$/', $requestPath)) {
    // Translations endpoint
    new TranslationsController();
} else {
    // Unknown endpoint
    setupApiHeaders();
    sendJsonResponse(['error' => 'Endpoint not found'], 404);
}
