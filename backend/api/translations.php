<?php

namespace api;

use Bramus\Router\Router;
use models\TranslationUnit;
use models\TranslationHistory;


// Handle GET request, return single Translation Unit
$router->get('/translations/{id}', function ($id) {
    $unit = new TranslationUnit();
    echo $unit->get($id);
});

// Handle GET request, return change history for Translation Unit
$router->get('/history/{id}', function ($id) {
    $history = new TranslationHistory();
    echo $history->findByUnitId($id);
});

// Handle DELETE request
$router->delete('/translations/{id}', function ($id) {
    $unit = new TranslationUnit();
    echo $unit->delete($id);
});

// Handle GET request, return list of all Translation Units
$router->get('/translations', function () {
    $unit = new TranslationUnit();
    echo $unit->list();
});

// Handle POST request, add or update Translation Unit
$router->post('/translations', function () {
    $data = file_get_contents("php://input");
    $data = json_decode($data, true);

    $unit = new TranslationUnit();
    $id = isset($data['id']) ? $data['id'] : null;

    echo $unit->save($data, $id);
});