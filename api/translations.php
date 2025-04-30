<?php
require '../src/TranslationUnit.php';

header("Content-Type: application/json");
$manager = new TranslationUnit();

$method = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$last = end($uri);

// Allow access only to this file: /api/translations.php
if ($last !== 'translations.php') {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid endpoint']);
    exit;
}

// GET /api/translations.php → list all
if ($method === 'GET' && !isset($_GET['id']) && !isset($_GET['history'])) {
    $translations = $manager->getAllUnits();
    echo json_encode($translations);
}

// POST /api/translations.php → add new translation
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['source'], $data['translated'], $data['source_language'], $data['target_language'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $unit = $manager->findOrCreateUnit($data['source'], $data['source_language']);
    $translationId = $manager->addTranslationToUnit(
        $unit['id'],
        $data['translated'],
        $data['target_language']
    );

    echo json_encode([
        'message' => 'Translation added to unit',
        'unit_id' => $unit['id'],
        'translation_id' => $translationId
    ]);
}

// GET /api/translations.php?id=1 → get unit with translations
elseif ($method === 'GET' && isset($_GET['id']) && !isset($_GET['history'])) {
    $unit = $manager->getUnitById((int)$_GET['id']);
    echo json_encode($unit ?: ['error' => 'Unit not found']);
}

// GET /api/translations.php?translation_id=5&history=1 → get history for one translation
elseif ($method === 'GET' && isset($_GET['translation_id'], $_GET['history']) && $_GET['history'] == 1) {
    $history = $manager->getTranslationHistory((int)$_GET['translation_id']);
    echo json_encode($history ?: ['message' => 'No history found']);
}

// DELETE /api/translations.php?id=1 → delete the entire unit and all its translations
elseif ($method === 'DELETE' && isset($_GET['id'])) {
    $success = $manager->deleteUnit((int)$_GET['id']);
    echo json_encode($success ? ['message' => 'Unit deleted'] : ['error' => 'Delete failed']);
}

// PUT /api/translations.php?id=1&translation_id=3 → update source + translation
elseif ($method === 'PUT' && isset($_GET['id'], $_GET['translation_id'])) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!isset($data['source'], $data['source_language'], $data['translated'], $data['target_language'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $success = $manager->updateUnitAndTranslation(
        (int)$_GET['id'],
        (int)$_GET['translation_id'],
        $data['source'],
        $data['source_language'],
        $data['translated'],
        $data['target_language']
    );

    echo json_encode($success ? ['message' => 'Unit and translation updated'] : ['error' => 'Update failed']);
}

// Fallback
else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed or invalid route']);
}
?>
