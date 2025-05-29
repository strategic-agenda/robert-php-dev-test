<?php

// Implement the RESTful API endpoints for translation units.
require_once '../src/TranslationUnit.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

$db = getDatabase();
$unitManager = new TranslationUnit($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            // Get specific translation unit
            $stmt = $db->prepare('SELECT * FROM translation_units WHERE id = ?');
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch());
        } else {
            // Get all translation units
            $stmt = $db->query('SELECT * FROM translation_units ORDER BY created_at DESC');
            echo json_encode($stmt->fetchAll());
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['source_text'])) {
            http_response_code(400);
            echo json_encode(['error' => 'source_text is required']);
            break;
        }

        $stmt = $db->prepare('INSERT INTO translation_units (source_text, target_text) VALUES (?, ?)');
        $stmt->execute([$data['source_text'], $data['target_text'] ?? null]);
        
        echo json_encode([
            'id' => $db->lastInsertId(),
            'message' => 'Translation unit created successfully'
        ]);
        break;

    case 'PUT':
        parse_str(file_get_contents('php://input'), $putData);
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
            break;
        }

        // Get current translation for history
        $stmt = $db->prepare('SELECT target_text FROM translation_units WHERE id = ?');
        $stmt->execute([$_GET['id']]);
        $current = $stmt->fetch();

        if ($current && $current['target_text']) {
            // Save to history
            $stmt = $db->prepare('INSERT INTO translation_history (unit_id, previous_target_text) VALUES (?, ?)');
            $stmt->execute([$_GET['id'], $current['target_text']]);
        }

        // Update translation
        $stmt = $db->prepare('UPDATE translation_units SET target_text = ? WHERE id = ?');
        $success = $stmt->execute([$data['target_text'], $_GET['id']]);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Translation updated successfully' : 'Failed to update translation'
        ]);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required']);
            break;
        }

        $stmt = $db->prepare('DELETE FROM translation_units WHERE id = ?');
        $success = $stmt->execute([$_GET['id']]);
        
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Translation deleted successfully' : 'Failed to delete translation'
        ]);
        break;

    case 'OPTIONS':
        // Handle preflight requests
        http_response_code(200);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
