<?php

// Implement the RESTful API endpoints for translation units.

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/TranslationUnit.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

// Add CORS middleware
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Handle preflight OPTIONS requests
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
$errorHandler->forceContentType('application/json');

$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response->withHeader('Content-Type', 'application/json');
});

$dbConfig = [
    'host' => getenv('DB_HOST') ?: 'db',
    'port' => getenv('DB_PORT') ?: 3306,
    'dbname' => getenv('DB_NAME') ?: 'translations_db',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: 'secret',
    'charset' => 'utf8mb4'
];

$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$db = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
  PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);

$transactionService = new App\TranslationUnit($db);

$app->get('/api/units', function (Request $request, Response $response) use ($transactionService) {
    $units = $transactionService->listUnits();
    $response->getBody()->write(json_encode($units));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/api/units/{id}', function (Request $request, Response $response, array $args) use ($transactionService) {
    $unit = $transactionService->getUnit($args['id']);
    if (!$unit) {
        return $response->withStatus(404);
    }
    $response->getBody()->write(json_encode($unit));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/units', function (Request $request, Response $response) use ($transactionService) {
    $data = $request->getParsedBody();
    
    if (!isset($data['source'])) {
        return $response->withStatus(400);
    }

    $id = uniqid();
    $translations = $data['translations'] ?? [];
    
    if ($transactionService->addUnit($id, $data['source'], $translations)) {
        $unit = $transactionService->getUnit($id);
        $response->getBody()->write(json_encode($unit));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    return $response->withStatus(500);
});

$app->put('/api/units/{id}', function (Request $request, Response $response, array $args) use ($transactionService) {
    $data = $request->getParsedBody();
    
    if (!isset($data['translations'])) {
        return $response->withStatus(400);
    }

    $success = true;
    foreach ($data['translations'] as $lang => $text) {
        if (!$transactionService->updateTranslation($args['id'], $lang, $text)) {
            $success = false;
            break;
        }
    }

    if ($success) {
        $unit = $transactionService->getUnit($args['id']);
        $response->getBody()->write(json_encode($unit));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    return $response->withStatus(404);
});

$app->delete('/api/units/{id}', function (Request $request, Response $response, array $args) use ($transactionService) {
    if ($transactionService->deleteUnit($args['id'])) {
        return $response->withStatus(204);
    }
    return $response->withStatus(404);
});

$app->get('/api/units/{id}/history', function (Request $request, Response $response, array $args) use ($transactionService) {
    $history = $transactionService->getHistory($args['id']);
    $response->getBody()->write(json_encode($history));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run(); 
