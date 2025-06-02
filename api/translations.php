<?php


require_once __DIR__ . '/../src/TranslationUnit.php';

header('Content-Type: application/json');

// Get HTTP method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = trim($_SERVER['REQUEST_URI'], '/');

// Parse ID if present (e.g. /units/3)
$segments = explode('/', $path);
$resource = $segments[0] ?? null;
$id = $segments[1] ?? null;

// Read raw data from the request body
$input = json_decode(file_get_contents('php://input'), true);

// Route the request
if ($resource === 'units') {
    switch ($method) {
        case 'GET':
            if ($id) {
                $unit = TranslationUnit::getById((int)$id);
                if ($unit) {
                    echo json_encode([
                        'id' => $unit->getId(),
                        'content' => $unit->getContent(),
                        'history' => $unit->getHistory()
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Translation unit not found']);
                }
            } else {
                $units = TranslationUnit::all();
                $all = array_map(fn($u) => [
                    'id' => $u->getId(),
                    'content' => $u->getContent(),
                    'history' => $u->getHistory()
                ], $units);
                echo json_encode($all);
            }
            break;

        case 'POST':
            if (isset($input['content'])) {
                $unit = TranslationUnit::add($input['content']);
                echo json_encode([
                    'id' => $unit->getId(),
                    'content' => $unit->getContent()
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Content is required']);
            }
            break;

        case 'PUT':
            if ($id && isset($input['content'])) {
                $unit = TranslationUnit::getById((int)$id);
                if ($unit) {
                    $unit->update($input['content']);
                    echo json_encode([
                        'id' => $unit->getId(),
                        'content' => $unit->getContent(),
                        'history' => $unit->getHistory()
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Translation unit not found']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID and content are required']);
            }
            break;

        case 'DELETE':
            if ($id) {
                $deleted = TranslationUnit::delete((int)$id);
                if ($deleted) {
                    echo json_encode(['message' => 'Deleted']);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Not found']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid endpoint']);
}
