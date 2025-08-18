<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../src/TranslationUnitManager.php';

$manager = new TranslationUnitManager();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            if(isset($_GET['history'])) {
                $unitId = (int) $_GET['id'];
                echo json_encode($manager->getHistoryByUnitId($unitId));
                exit;
            }

            $unit = $manager->getUnit((int)$_GET['id']);
            if ($unit) {
                echo json_encode([
                    'id' => $unit->id,
                    'source_text' => $unit->sourceText,
                    'translated_text' => $unit->translatedText,
                    'language_from' => $unit->languageFrom,
                    'language_to' => $unit->languageTo
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Translation not found']);
            }
        } else {
            $units = $manager->getAllAsArray();
            echo json_encode($units);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (
            isset($data['source_text'], $data['translated_text'], $data['language_from'], $data['language_to']) &&
            !empty($data['source_text']) && !empty($data['translated_text'])
        ) {
            $newId = $manager->addUnit(
                $data['source_text'],
                $data['translated_text'],
                $data['language_from'],
                $data['language_to']
            );
            http_response_code(201);
            echo json_encode(['message' => 'Translation created', 'id' => $newId]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['id'], $data['source_text'], $data['translated_text']) && !empty($data['source_text']) && !empty($data['translated_text'])) {
            $updated = $manager->updateUnit((int)$data['id'], $data['source_text'], $data['translated_text']);
            if ($updated) {
                echo json_encode(['message' => 'Translation updated']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Translation not found']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}
