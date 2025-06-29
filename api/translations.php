<?php
// dealing with cors
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

require_once '../src/Controller/TranslationUnitController.php';
require_once '../src/Controller/TranslationController.php';
require_once '../src/Service/TranslationService.php';
require_once '../src/Repository/TranslationUnitRepository.php';
require_once '../src/Repository/TranslationRepository.php';
require_once '../src/Database.php';
require_once '../src/Controller/LanguageController.php';

// database
$db = new \CAT\Database('cat_postgres', 'catdb', 'catuser', 'catpass');
$db->initializeSchema();

// work units
$unitRepository = new \CAT\Repository\TranslationUnitRepository($db);
$translationRepository = new \CAT\Repository\TranslationRepository($db);
$service = new \CAT\Service\TranslationService($unitRepository, $translationRepository);
$unitController = new \CAT\Controller\TranslationUnitController($service);
$translationController = new \CAT\Controller\TranslationController($service);
$languageController = new \CAT\Controller\LanguageController($db);

// Global Content-Type check for non-GET methods
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') === false) {
        sendJson(['error' => 'Content-Type must be application/json'], 415);
    }
}

// route handling
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function sendJson($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data);
    exit;
}

$routes = [
    ['GET',    '/api/translation-units', fn() => $unitController->list()],
    ['POST',   '/api/translation-units', fn() => $unitController->create()],
    ['GET',    '/api/translation-units/(\d+)', fn($id) => $unitController->get((int)$id)],
    ['PUT',    '/api/translation-units/(\d+)', fn($id) => $unitController->update((int)$id)],
    ['DELETE', '/api/translation-units/(\d+)', fn($id) => $unitController->delete((int)$id)],
    ['GET',    '/api/translation-units/(\d+)/translations', fn($id) => $translationController->listForUnit((int)$id)],
    ['POST',   '/api/translations', fn() => $translationController->create()],
    ['GET',    '/api/translations/(\d+)', fn($id) => $translationController->get((int)$id)],
    ['PUT',    '/api/translations/(\d+)', fn($id) => $translationController->update((int)$id)],
    ['DELETE', '/api/translations/(\d+)', fn($id) => $translationController->delete((int)$id)],
    ['GET',    '/api/languages', fn() => $languageController->list()],
];

$found = false;
foreach ($routes as [$routeMethod, $pattern, $handler]) {
    $regex = '#^' . $pattern . '/?$#';
    if ($method === $routeMethod && preg_match($regex, $uri, $matches)) {
        $found = true;
        array_shift($matches);
        $handler(...$matches);
        break;
    }
}
if (!$found) {
    sendJson(['error' => 'Not Found'], 404);
}
