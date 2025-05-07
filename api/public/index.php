<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../src/TranslationUnit.php';
require_once __DIR__ . '/../src/Language.php';

// Get request body for POST/PUT requests
$requestBody = json_decode(file_get_contents('php://input'), true);

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Parse URL to extract ID if present
$requestParts = explode('/', trim($request, '/'));

// Debug output
// echo json_encode(['request' => $request, 'parts' => $requestParts]);
// exit;

// Simple router
if (isset($requestParts[0]) && $requestParts[0] === 'api') {
    $endpoint = $requestParts[1] ?? '';
    $id = $requestParts[2] ?? null;
    
    switch ($endpoint) {
        case 'languages':
            if ($method === 'GET') {
                // Get all languages or a specific language
                if ($id) {
                    $language = Language::findByCode($id);
                    if ($language) {
                        echo json_encode($language->toArray());
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Language not found']);
                    }
                } else {
                    $languages = Language::findAll();
                    $result = [];
                    
                    foreach ($languages as $language) {
                        $result[] = $language->toArray();
                    }
                    
                    echo json_encode($result);
                }
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed for languages endpoint']);
            }
            break;
            
        case 'units':
            if ($method === 'GET') {
                if ($id) {
                    // Get single unit
                    $unit = TranslationUnit::findById($id);
                    if ($unit) {
                        echo json_encode($unit->toArray());
                    } else {
                        http_response_code(404);
                        echo json_encode(['error' => 'Unit not found']);
                    }
                } else {
                    // Get all units
                    $units = TranslationUnit::findAll();
                    $result = [];
                    
                    foreach ($units as $unit) {
                        $result[] = $unit->toArray();
                    }
                    
                    echo json_encode($result);
                }
            } elseif ($method === 'POST') {
                // Create new unit
                if (!$requestBody) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid request body']);
                    exit;
                }
                
                $unit = new TranslationUnit(
                    $requestBody['sourceText'] ?? null,
                    $requestBody['targetText'] ?? null,
                    $requestBody['sourceLanguage'] ?? null,
                    $requestBody['targetLanguage'] ?? null
                );
                
                $unit->save();
                
                http_response_code(201);
                echo json_encode($unit->toArray());
            } elseif ($method === 'PUT' && $id) {
                // Update existing unit
                $unit = TranslationUnit::findById($id);
                
                if (!$unit) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Unit not found']);
                    exit;
                }
                
                if (isset($requestBody['targetText'])) {
                    $unit->updateTargetText($requestBody['targetText']);
                }
                
                echo json_encode($unit->toArray());
            } elseif ($method === 'DELETE' && $id) {
                // Delete unit
                $unit = TranslationUnit::findById($id);
                
                if (!$unit) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Unit not found']);
                    exit;
                }
                
                // TODO: Implement delete method in TranslationUnit class
                
                http_response_code(204); // No content
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
        
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} else {
    // Return a more descriptive error
    http_response_code(404);
    echo json_encode([
        'error' => 'API not found',
        'request' => $request,
        'requestParts' => $requestParts
    ]);
} 