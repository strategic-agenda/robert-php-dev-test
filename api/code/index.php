<?php

header("Access-Control-Allow-Origin: http://www.interview.localhost");

require 'vendor/autoload.php';

$server = new \Kirilmaz\Interview\Core\Server();
$router = new \Kirilmaz\Interview\Core\Router();

/**
 * Routes for API
 */
$router->add('/initialise', 'get', 'InitialiseController');
$router->add('/languages', 'get', 'CustomerLanguageListController');
$router->add('/system/translations', 'get', 'SystemTranslationsController');
$router->add('/system/language', 'get', 'SystemLanguageListController');
$router->add('/system/language/{uuid}', 'get', 'SystemTranslationsController');
$router->add('/translations', 'get', 'CustomerTranslationsController');
$router->add('/translations/all', 'get', 'CustomerTranslationGetAllController');
$router->add('/translations/grouped', 'get', 'CustomerTranslationGetAllGroupedByTranslationKeyController');
$router->add('/translations/{uuid}', 'get', 'CustomerTranslationDetailController');
$router->add('/translations', 'post', 'CustomerTranslationSaveController');
$router->add('/translations/{uuid}', 'put', 'CustomerTranslationUpdateController');

/**
 * Get URI & HTTP method
 */
$uri = $server->get('REQUEST_URI');
$httpMethod = $server->get('REQUEST_METHOD');

/**
 * Instantiate URI Matcher & Match URIs
 * @param \Kirilmaz\Interview\Core\Router $router
 * @param \Kirilmaz\Interview\Core\UriParser $parser
 * @param string $uri           URI
 * @param string $httpMethod    Http Method
 */
$uriMatcher = new \Kirilmaz\Interview\Core\UriMatcher(
    $router,
    $uriParser = new \Kirilmaz\Interview\Core\UriParser($uri, $httpMethod)
);

/**
 * Match URI with route
 */
$uriMatcher->match();

/**
 * Get controller & variables
 */
$controller = $uriMatcher->controller();
$variables = $uriMatcher->variables();

/**
 * Instantiate controller if controller exists
 * @param object $variables
 */
if ($controller) {
    $controller = 'Kirilmaz\\Interview\\Controllers\\' . $controller;
    $controller = new $controller();
    echo $controller->handle($variables);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Not allowed'
]);

unset($server);
unset($router);
unset($uriParser);
