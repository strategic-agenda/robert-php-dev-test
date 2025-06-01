<?php
require_once __DIR__ . '/../src/TranslationUnit.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['target_text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing translated_text']);
    exit;
}

$translationUnit = new TranslationUnit();
$translationUnit->updateTranslationUnit((int)$id, $data['target_text'], $data['translated_by'], $data['notes']);
 echo json_encode(['message' => "Translation Unit Updated"]);
