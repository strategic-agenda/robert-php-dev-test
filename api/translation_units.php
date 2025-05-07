<?php
require_once './database_connection.php';
require_once './response_headers.php';
require_once '../src/TranslationUnit.php';

$translationUnitHandler = new TranslationUnit($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
$id = $_GET['id'] ?? null;

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                if (!is_numeric($id)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid ID format']);
                    break;
                }
                $result = $translationUnitHandler->getById($id);
                if (!$result) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Translation Unit not found']);
                } else {
                    echo json_encode($result);
                }
            } else {
                $result = $translationUnitHandler->getAll();
                echo json_encode($result);
            }
            break;

        case 'POST':
            $source = trim($input['source'] ?? '');
            $source_language_id = $input['source_language_id'] ?? null;

            if (empty($source) || empty($source_language_id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing fields']);
                break;
            }

            if (!is_numeric($source_language_id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid language ID']);
                break;
            }

            $newId = $translationUnitHandler->create($source, $source_language_id);
            http_response_code(201);
            echo json_encode(['message' => 'Translation Unit has been created.', 'id' => $newId]);
            break;

        case 'PUT':
            if (!$id || empty($input['source']) || empty($input['source_language_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID or source missing']);
                break;
            }

            $source = trim($input['source']);
            $source_language_id = $input['source_language_id'];

            if (empty($source) || !is_numeric($source_language_id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid input data']);
                break;
            }

            $translationUnitHandler->update($id, $source, $source_language_id);
            echo json_encode(['message' => 'Translation Unit has been updated.']);
            break;

        case 'DELETE':
            if (!$id || !is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID is required and must be numeric']);
                break;
            }
            $translationUnitHandler->delete($id);
            echo json_encode(['message' => 'Translation Unit has been deleted.']);
            break;

        case 'OPTIONS':
            http_response_code(204);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Unexpected error', 'details' => $e->getMessage()]);
}
