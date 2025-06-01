<?php
require_once __DIR__ . '/../src/TranslationUnit.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', $uri);
$id = end($parts);

if (!is_numeric($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$translationUnit = new TranslationUnit();
$deleted = $translationUnit->deleteTranslationUnit((int)$id);

if ($deleted) {
    echo json_encode(['message' => 'Translation unit deleted']);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Translation unit not found']);
}
