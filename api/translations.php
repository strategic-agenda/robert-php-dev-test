<?php

// Implement the RESTful API endpoints for translation units.
require_once '../vendor/autoload.php';

use AwaisAmir\RobertPhpDevTest\TranslationUnit;

$translationManager = new TranslationUnit();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $unitId = $_GET['id'];
    $translationUnit = $translationManager->getTranslationUnitById($unitId);

    if ($translationUnit) {
        header('Content-Type: application/json');
        echo json_encode($translationUnit);
        exit();
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Translation unit not found"]);
        exit();
    }
}

// Handle HTTP GET request to retrieve all units
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $translationUnits = $translationManager->getAllTranslationUnits();

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($translationUnits);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = file_get_contents("php://input");

    if (empty($jsonInput)) {
        // Handle empty request body
        http_response_code(400);
        echo json_encode(["message" => "Empty request body"]);
        exit();
    }

    $data = json_decode($jsonInput, true);

    if (!isset($data['text'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit();
    }

    // Validate and sanitize the data
    $sourceText = htmlspecialchars($data['text']);

    if ($sourceText === false) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input data"]);
        exit();
    }

    $newUnit = $translationManager->addTranslationUnit(
        $sourceText
    );

    header('Content-Type: application/json');
    echo json_encode($newUnit);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $jsonInput = file_get_contents("php://input");

    if (empty($jsonInput)) {
        // Handle empty request body
        http_response_code(400);
        echo json_encode(["message" => "Empty request body"]);
        exit();
    }

    $data = json_decode($jsonInput, true);

    if (!isset($data['id']) || !isset($data['text'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit();
    }

    $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    $newSourceText = htmlspecialchars($data['text']);

    if ($id === false || $id <= 0) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input data"]);
        exit();
    }

    try {
        $translationManager->updateTranslationUnit($id, $newSourceText);

        header('Content-Type: application/json');
        echo json_encode(["message" => "Translation unit updated successfully"]);
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(
            ["message" => "An error occurred while updating the translation unit"]
        );
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $unitId = $_GET['id'];

    try {
        $translationManager->deleteTranslationUnit($unitId);
        http_response_code(204);
        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(
            ["message" => "An error occurred while deleting the translation unit"]
        );
        exit();
    }
}
