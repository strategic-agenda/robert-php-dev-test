<?php
require_once './database_connection.php';
require_once './response_headers.php';
require_once '../src/Language.php';

$languageHandler = new Language($pdo);

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $result = $languageHandler->getAll();
            if (empty($result)) {
                http_response_code(200);
                echo json_encode([]);
            } else {
                http_response_code(200);
                echo json_encode($result);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected error', 'details' => $e->getMessage()]);
}
