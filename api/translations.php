<?php

require_once __DIR__ . '/config/database.php';

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Handle GET request (Retrieve translations)
        $stmt = $conn->query("SELECT * FROM translations");
        $translations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($translations);
        break;

    case 'POST':
        // Handle POST request (Create Translation)
        $data = json_decode(file_get_contents("php://input"));
        $sql = "INSERT INTO translations (source, translation) VALUES (:source, :translation)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':source', $data->source);
        $stmt->bindParam(':translation', $data->translation);
        if($stmt->execute()) {
            echo json_encode(['message' => 'Translation created successfully']);
        }
        break;

    case 'PUT':
        // Handle PUT request (Update Translation)
        $data = json_decode(file_get_contents("php://input"));
        $sql = "UPDATE translations SET source = :source, translation = :translation WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':source', $data->source);
        $stmt->bindParam(':translation', $data->translation);
        $stmt->bindParam(':id', $data->id);
        if($stmt->execute()) {
            echo json_encode(['message' => 'Translation updated successfully']);
        }
        break;

    case 'DELETE':
        // Handle DELETE request (Delete Translation)
        $data = json_decode(file_get_contents("php://input"));
        $sql = "DELETE FROM translations WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $data->id);
        if($stmt->execute()) {
            echo json_encode(['message' => 'Translation deleted successfully']);
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        break;
}
?>