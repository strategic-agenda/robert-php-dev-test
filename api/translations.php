<?php
require_once './database_connection.php';
require_once './response_headers.php';
require_once '../src/Translation.php';

$translationHandler = new Translation($pdo);

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

                $result = $translationHandler->getById($id);
                if (!$result) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Translation not found']);
                } else {
                    echo json_encode($result);
                }
            } else {
                if (empty($_GET['unit_id']) || !is_numeric($_GET['unit_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid or missing unit_id']);
                    break;
                }
                $result = $translationHandler->getAllByUnitId($_GET['unit_id']);
                echo json_encode($result);
            }
            break;

        case 'POST':
            // Sanitize input and validate required fields
            $unitId = $_GET['translation_unit_id'] ?? null;
            $translatedText = $input['translated_text'] ?? '';
            $languageId = $input['translated_language_id'] ?? null;

            if (empty($unitId) || !is_numeric($unitId) || empty($translatedText) || empty($languageId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing or invalid fields']);
                break;
            }

            $newId = $translationHandler->create($unitId, $translatedText, $languageId);
            http_response_code(201);  // Created
            echo json_encode(['message' => 'Translation has been created.', 'id' => $newId]);
            break;

        case 'PUT':
            if (!$id || empty($input['translated_text']) || empty($input['translated_language_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID or required fields missing']);
                break;
            }

            $translatedText = trim($input['translated_text']);
            $translatedLanguageId = $input['translated_language_id'];

            if (empty($translatedText) || !is_numeric($translatedLanguageId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid translation data']);
                break;
            }

            $translationHandler->update($id, $translatedText, $translatedLanguageId);
            echo json_encode(['message' => 'Translation has been updated.']);
            break;

        case 'DELETE':
            if (!$id || !is_numeric($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID is required and must be numeric']);
                break;
            }
            $translationHandler->delete($id);
            echo json_encode(['message' => 'Translation has been deleted.']);
            break;

        case 'OPTIONS':
            // For CORS preflight
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
