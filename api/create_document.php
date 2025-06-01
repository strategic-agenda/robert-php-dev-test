<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/TranslationUnit.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode(['error' => 'Only POST method is allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['name'], $input['source_language'], $input['target_language'])) {
    http_response_code(400); 
    echo json_encode(['error' => 'Missing required fields: name, source_language, target_language']);
    exit;
}

try {
    $translationUnit = new TranslationUnit();
    $documentId = $translationUnit->createDocument(
        $input['name'],
        $input['source_language'],
        $input['target_language']
    );

    echo json_encode([
        'success' => true,
        'document_id' => $documentId
    ]);
} catch (Exception $e) {
    http_response_code(404); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}
