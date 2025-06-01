<?php
require_once __DIR__ . '/../src/TranslationUnit.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (
    empty($data['document_id']) ||
    empty($data['unit_index']) ||
    empty($data['source_text']) ||
    empty($data['target_text']) 
) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$translationUnit = new TranslationUnit();
$unitId = $translationUnit->addTranslationUnit(
    $data['document_id'],
    $data['unit_index'],
    $data['source_text'],
    $data['target_text']
);

if ($unitId) {
    http_response_code(201);
    echo json_encode(['message' => 'Translation unit created', 'id' => $unitId]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create translation unit']);
}
