<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../src/Repository/TranslationUnitRepository.php';

use App\Repository\TranslationUnitRepository;

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$repo = new TranslationUnitRepository();

$method = $_SERVER['REQUEST_METHOD'];
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['history'])) {
                $translationId = (int) $_GET['history'];
                $data = $repo->getTranslationHistory($translationId);
                echo json_encode($data);
                break;
            }

            if (isset($_GET['id'])) {
                $data = $repo->getUnitById((int) $_GET['id']);
                if (empty($data)) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Unit not found.']);
                    break;
                }
            } else {
                $data = $repo->getAllUnits();
            }
            echo json_encode($data);
            break;


        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['source_text']) || empty($input['source_lang_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'source_text and source_lang_id are required.']);
                break;
            }
            $id = $repo->addUnit($input['source_text'], (int)$input['source_lang_id']);
            http_response_code(201);
            echo json_encode(['success' => true, 'unit_id' => $id]);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['unit_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'unit_id is required.']);
                break;
            }


            if (!empty($input['source_text'])) {
                $repo->updateSourceText((int)$input['unit_id'], $input['source_text']);
            }


            if (!empty($input['target_lang_id']) && !empty($input['translated_text'])) {
                $repo->updateTranslation(
                    (int)$input['unit_id'],
                    (int)$input['target_lang_id'],
                    $input['translated_text']
                );
            }

            echo json_encode(['success' => true]);
            break;



        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'id is required for DELETE.']);
                break;
            }
            $repo->deleteUnit((int)$_GET['id']);
            http_response_code(204);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
