<?php
require_once __DIR__ . '/../src/TranslationUnit.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$translationUnit = new TranslationUnit();
$units = $translationUnit->getAllTranslationUnits();

if ($units) {
    echo json_encode($units);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Translation units not found']);
}