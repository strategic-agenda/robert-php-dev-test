<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$request = $_SERVER['REQUEST_URI'];


switch (true) {
  
    case $request === '/api/create_document' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require __DIR__ . '/../api/create_document.php';
        break;

    case preg_match('/\/api\/unit\/(\d+)/', $request, $matches) && $_SERVER['REQUEST_METHOD'] === 'GET':
        require __DIR__ . '/../api/get.php';
        break;

    case $request === '/api/unit' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require __DIR__ . '/../api/post.php';
        break;

    case preg_match('/\/api\/unit\/(\d+)/', $request, $matches) && $_SERVER['REQUEST_METHOD'] === 'PUT':
        require __DIR__ . '/../api/put.php';
        break;

    case preg_match('/\/api\/unit\/(\d+)/', $request, $matches) && $_SERVER['REQUEST_METHOD'] === 'DELETE':
        require __DIR__ . '/../api/delete.php';
        break;

    case $request === '/api/units' && $_SERVER['REQUEST_METHOD'] === 'GET':
    require __DIR__ . '/../api/list_translation_units.php';
    break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
}
