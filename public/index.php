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

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$container = new Container();

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->addBodyParsingMiddleware();

$app->addErrorMiddleware(true, true, true);

$container->set('db', function() {
    return DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'port' => $_ENV['DB_PORT'] ?? '3306',
        'dbname' => $_ENV['DB_NAME'] ?? 'translation_db',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4'
    ]);
});

$container->set(TranslationUnitRepository::class, function($container) {
    return new TranslationUnitRepository($container->get('db'));
});

$container->set(TranslationUnitController::class, function($container) {
    return new TranslationUnitController($container->get(TranslationUnitRepository::class));
});

$app->get('/api/translation-units', [TranslationUnitController::class, 'list']);
$app->get('/api/translation-units/{id}', [TranslationUnitController::class, 'get']);
$app->post('/api/translation-units', [TranslationUnitController::class, 'create']);
$app->put('/api/translation-units/{id}', [TranslationUnitController::class, 'update']);
$app->delete('/api/translation-units/{id}', [TranslationUnitController::class, 'delete']);

$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->run();
