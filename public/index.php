<?php

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

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

// Add Error Middleware
$app->addErrorMiddleware(true, true, true);

// Add CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// API Routes
$app->group('/api', function (RouteCollectorProxy $group) {
    // Translation routes
    $group->get('/translations', 'App\Controllers\TranslationController:getAll');
    $group->get('/translations/{id}', 'App\Controllers\TranslationController:getOne');
    $group->post('/translations', 'App\Controllers\TranslationController:create');
    $group->put('/translations/{id}', 'App\Controllers\TranslationController:update');
    $group->delete('/translations/{id}', 'App\Controllers\TranslationController:delete');
});

// Run app
$app->run(); 