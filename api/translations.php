<?php

use Lampdev\RobertPhpDevTest\Database;
use Lampdev\RobertPhpDevTest\TranslationUnit;
use Lampdev\RobertPhpDevTest\TranslationUnitManager;

require_once '../vendor/autoload.php';

$database = new Database();
$db = $database->getConnection();

$translationUnitManager = new TranslationUnit($db);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $unitId = $_GET['id'];
    $translationUnit = $translationUnitManager->getTranslationUnit($unitId);

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
    $translationUnits = $translationUnitManager->getAllTranslationUnits();

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

    if ($data === null) {
        // Handle JSON parsing error
        http_response_code(400);
        echo json_encode(["message" => "Invalid JSON data"]);
        exit();
    }

    if (!isset($data['source_text']) || !isset($data['language_id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit();
    }

    // Validate and sanitize the data
    $sourceText = htmlspecialchars($data['source_text']);
    $languageId = filter_var($data['language_id'], FILTER_VALIDATE_INT);

    if ($sourceText === false || $languageId === false || $languageId <= 0) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input data"]);
        exit();
    }

    $newUnit = $translationUnitManager->addTranslationUnit(
        $sourceText,
        $languageId
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

    if ($data === null) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid JSON data"]);
        exit();
    }

    if (!isset($data['id']) || !isset($data['source_text'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit();
    }

    $id = filter_var($data['id'], FILTER_VALIDATE_INT);
    $newSourceText = htmlspecialchars($data['source_text']);

    if ($id === false || $id <= 0) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input data"]);
        exit();
    }

    try {
        $translationUnitManager->updateTranslationUnit($id, $newSourceText);

        header('Content-Type: application/json');
        echo json_encode(["message" => "Translation unit updated successfully"]
        );
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
        $translationUnitManager->deleteTranslationUnit($unitId);
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
