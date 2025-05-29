<?php

declare(strict_types=1);

namespace App\Api;

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$controller = new TranslationUnitController();

try {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = trim($path, '/');
    $segments = explode('/', $path);

    // Remove 'api/v1' from the path
    if ($segments[0] === 'api' && $segments[1] === 'v1') {
        array_shift($segments);
        array_shift($segments);
    }

    $resource = $segments[0] ?? '';
    $id = $segments[1] ?? null;
    $action = $segments[2] ?? null;

    if ($resource !== 'translation-units') {
        throw new InvalidArgumentException('Invalid resource');
    }

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if ($id === null) {
                // List translation units
                $response = $controller->list($_GET);
            } elseif ($action === 'history') {
                // Get translation history
                $response = $controller->getHistory($id);
            } else {
                // Get specific translation unit
                $response = $controller->get($id);
            }
            break;

        case 'POST':
            if ($id !== null) {
                throw new InvalidArgumentException('Invalid endpoint');
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $controller->create($data);
            break;

        case 'PUT':
            if ($id === null) {
                throw new InvalidArgumentException('Missing ID');
            }
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $controller->update($id, $data);
            break;

        case 'DELETE':
            if ($id === null) {
                throw new InvalidArgumentException('Missing ID');
            }
            $controller->delete($id);
            $response = ['message' => 'Translation unit deleted successfully'];
            break;

        default:
            throw new InvalidArgumentException('Method not allowed');
    }

    echo json_encode($response);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
} 