<?php

use DI\Container;
use Slim\Factory\AppFactory;
use App\Controller\TranslationUnitController;
use App\Repository\TranslationUnitRepository;
use Doctrine\DBAL\DriverManager;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Middleware\BodyParsingMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();

// Set container to create App with on AppFactory
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add CORS middleware
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Add JSON parsing middleware
$app->addBodyParsingMiddleware();

// Add Error Middleware
$app->addErrorMiddleware(true, true, true);

// Configure database connection
$container->set('db', function() {
    return DriverManager::getConnection([
        'driver' => 'pdo_pgsql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '5432',
        'dbname' => $_ENV['DB_NAME'] ?? 'translation_db',
        'user' => $_ENV['DB_USER'] ?? 'postgres',
        'password' => $_ENV['DB_PASSWORD'] ?? 'postgres',
    ]);
});

// Configure repositories
$container->set(TranslationUnitRepository::class, function($container) {
    return new TranslationUnitRepository($container->get('db'));
});

// Configure controllers
$container->set(TranslationUnitController::class, function($container) {
    return new TranslationUnitController($container->get(TranslationUnitRepository::class));
});

// Define routes
$app->get('/api/translation-units', [TranslationUnitController::class, 'list']);
$app->get('/api/translation-units/{id}', [TranslationUnitController::class, 'get']);
$app->post('/api/translation-units', [TranslationUnitController::class, 'create']);
$app->put('/api/translation-units/{id}', [TranslationUnitController::class, 'update']);
$app->delete('/api/translation-units/{id}', [TranslationUnitController::class, 'delete']);

// Add OPTIONS route for CORS preflight requests
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

// Run app
$app->run(); 