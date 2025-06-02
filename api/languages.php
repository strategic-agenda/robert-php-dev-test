<?php
require_once __DIR__ . '/../database/db.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getConnection();

try {
    switch ($method) {
        case 'GET':
            
            $stmt = $db->query("SELECT id, name, code FROM languages");
            $languages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($languages);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['name']) || empty($data['code'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Both name and code are required.']);
                break;
            }

            $stmt = $db->prepare("INSERT INTO languages (name, code) VALUES (:name, :code)");
            $stmt->execute([
                ':name' => $data['name'],
                ':code' => $data['code']
            ]);

            http_response_code(201);
            echo json_encode(['success' => true, 'language_id' => $db->lastInsertId()]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
