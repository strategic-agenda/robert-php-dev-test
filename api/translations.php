<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}
header("Content-Type: application/json; charset=UTF-8");


require_once '../src/ProjectManager.php';
require_once '../src/DocumentManager.php';
require_once '../src/TranslationUnit.php';


$projectManager = new ProjectManager();
$documentManager = new DocumentManager();
$translationManager = new TranslationUnit();


// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));


// Get resource type (first part of the URI)
$resource = array_shift($request);


// Authentication should be handled here
$userId = 1; // Mocking user ID 

try {
    // Handle different resource types
    switch ($resource) {
        case 'projects':
            handleProjectResource($method, $request, $projectManager);
            break;

        case 'documents':
            handleDocumentResource($method, $request, $documentManager, $projectManager);
            break;

        case 'translation-units':
            handleTranslationUnitResource($method, $request, $translationManager, $documentManager);
            break;

        case 'translation-history':
            handleTranslationHistoryResource($method, $request, $translationManager);
            break;

        default:
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'Resource not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500); 
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Handle project resource requests
 */
function handleProjectResource($method, $request, $projectManager)
{
    $id = $request[0] ?? null;

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get a specific project
                $project = getProject($id, $projectManager);
                if ($project) {
                    http_response_code(200); 
                    echo json_encode($project);
                } else {
                    http_response_code(404); 
                    echo json_encode(['error' => 'Project not found']);
                }
            } else {
                // List all projects
                $projects = getProjects($projectManager);
                http_response_code(200); 
                echo json_encode($projects);
            }
            break;

        case 'POST':
            // Create a new project
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (!isset($data['name']) || !isset($data['source_language']) || !isset($data['target_language'])) {
                http_response_code(400); // Bad Request
                echo json_encode(['error' => 'Missing required fields']);
                break;
            }

            $name = $data['name'];
            $sourceLanguage = $data['source_language'];
            $targetLanguage = $data['target_language'];

            $newId = $projectManager->createProject($name, $sourceLanguage, $targetLanguage);

            if ($newId) {
                http_response_code(201); // Created
                echo json_encode([
                    'id' => $newId,
                    'message' => 'Project created successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create project']);
            }
            break;

        case 'PUT':
            // Update a project
            if (!$id) {
                http_response_code(400); 
                echo json_encode(['error' => 'ID is required']);
                break;
            }

            // Check if the project exists
            if (!$projectManager->projectExists($id)) {
                http_response_code(404); 
                echo json_encode(['error' => 'Project not found']);
                break;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $success = updateProject($id, $data, $projectManager);

            if ($success) {
                http_response_code(200); 
                echo json_encode([
                    'message' => 'Project updated successfully'
                ]);
            } else {
                http_response_code(500); 
                echo json_encode(['error' => 'Failed to update project']);
            }
            break;

        case 'DELETE':
            
            if (!$id) {
                http_response_code(400); 
                echo json_encode(['error' => 'ID is required']);
                break;
            }

        
            if (!$projectManager->projectExists($id)) {
                http_response_code(404); 
                echo json_encode(['error' => 'Project not found']);
                break;
            }

            $success = $projectManager->deleteProject($id);

            if ($success) {
                http_response_code(200);
                echo json_encode([
                    'message' => 'Project deleted successfully'
                ]);
            } else {
                http_response_code(500); 
                echo json_encode(['error' => 'Failed to delete project']);
            }
            break;

        default:
            http_response_code(405); 
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

/**
 * Handle document resource requests
 */
function handleDocumentResource($method, $request, $documentManager, $projectManager)
{
    $id = $request[0] ?? null;
    $projectFilter = $_GET['project_id'] ?? null;

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get a specific document
                $document = getDocument($id, $documentManager);
                if ($document) {
                    http_response_code(200);
                    echo json_encode($document);
                } else {
                    http_response_code(404); 
                    echo json_encode(['error' => 'Document not found']);
                }
            } else {
                // List all documents, optionally filtered by project
                $documents = getDocuments($documentManager, $projectFilter);
                http_response_code(200); // OK
                echo json_encode($documents);
            }
            break;

        case 'POST':
            // Create a new document
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (!isset($data['project_id']) || !isset($data['name']) || !isset($data['path'])) {
                http_response_code(400); // Bad Request
                echo json_encode(['error' => 'Missing required fields']);
                break;
            }

            $projectId = $data['project_id'];
            $name = $data['name'];
            $path = $data['path'];

            // Check if project exists
            if (!$projectManager->projectExists($projectId)) {
                http_response_code(400); 
                echo json_encode(['error' => 'Project does not exist']);
                break;
            }

            $newId = $documentManager->createDocument($projectId, $name, $path);

            if ($newId) {
                http_response_code(201);
                echo json_encode([
                    'id' => $newId,
                    'message' => 'Document created successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create document']);
            }
            break;

        case 'PUT':
            // Update a document
            if (!$id) {
                http_response_code(400); 
                echo json_encode(['error' => 'ID is required']);
                break;
            }

            // Check if the document exists
            if (!$documentManager->documentExists($id)) {
                http_response_code(404); 
                echo json_encode(['error' => 'Document not found']);
                break;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            $success = updateDocument($id, $data, $documentManager);

            if ($success) {
                http_response_code(200);
                echo json_encode([
                    'message' => 'Document updated successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update document']);
            }
            break;

        case 'DELETE':
            // Delete a document
            if (!$id) {
                http_response_code(400); 
                echo json_encode(['error' => 'ID is required']);
                break;
            }

            // Check if the document exists
            if (!$documentManager->documentExists($id)) {
                http_response_code(404); 
                echo json_encode(['error' => 'Document not found']);
                break;
            }

            $success = $documentManager->deleteDocument($id);

            if ($success) {
                http_response_code(200); 
                echo json_encode([
                    'message' => 'Document deleted successfully'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete document']);
            }
            break;

        default:
            http_response_code(405); 
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

/**
 * Handle translation unit resource requests
 */
function handleTranslationUnitResource($method, $request, $translationManager, $documentManager)
{
    $id = $request[0] ?? null;
    $documentFilter = $_GET['document_id'] ?? null;

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get a specific translation unit
                $unit = $translationManager->getTranslationUnit($id);
                if ($unit) {
                    http_response_code(200); 
                    echo json_encode($unit);
                } else {
                    http_response_code(404); 
                    echo json_encode(['error' => 'Translation unit not found']);
                }
            } else {
                // List all translation units, optionally filtered by document
                $units = getTranslationUnits($translationManager, $documentFilter);
                http_response_code(200); 
                echo json_encode($units);
            }
            break;

        case 'POST':
            // Create a new translation unit
            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (!isset($data['document_id']) || !isset($data['source_text'])) {
                http_response_code(400); 
                echo json_encode(['error' => 'Missing required fields']);
                break;
            }

            $documentId = $data['document_id'];
            $sourceText = $data['source_text'];
            $targetText = $data['target_text'] ?? null;
            $status = $data['status'] ?? 'new';

            // Check if document exists
            if (!$documentManager->documentExists($documentId)) {
                http_response_code(400); 
                echo json_encode(['error' => 'Document does not exist']);
                break;
            }

            $newId = $translationManager->addTranslationUnit(
                $documentId,
                $sourceText,
                $targetText,
                $status
            );

            if ($newId) {
                http_response_code(201); 
                echo json_encode([
                    'id' => $newId,
                    'message' => 'Translation unit created successfully'
                ]);
            } else {
                http_response_code(500); 
                echo json_encode(['error' => 'Failed to create translation unit']);
            }
            break;

        case 'PUT':
            // Update an existing translation unit
            global $userId;

            if (!$id) {
                http_response_code(400); 
                echo json_encode(['error' => 'ID is required']);
                break;
            }

            // Check if the unit exists
            $unit = $translationManager->getTranslationUnit($id);
            if (!$unit) {
                http_response_code(404); 
                echo json_encode(['error' => 'Translation unit not found']);
                break;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Validate required fields
            if (!isset($data['target_text'])) {
                http_response_code(400); 
                echo json_encode(['error' => 'Missing target text']);
                break;
            }

            $newTargetText = $data['target_text'];
            $status = $data['status'] ?? null;

            $success = $translationManager->updateTranslationUnit(
                $id,
                $newTargetText,
                $userId,
                $status
            );

            if ($success) {
                http_response_code(200); 
                echo json_encode([
                    'message' => 'Translation unit updated successfully'
                ]);
            } else {
                http_response_code(500); 
                echo json_encode(['error' => 'Failed to update translation unit']);
            }
            break;

        case 'DELETE':
            // Delete a translation unit
            if (!$id) {
                http_response_code(400); 
                echo json_encode(['error' => 'ID is required']);
                break;
            }

            // Check if the unit exists
            $unit = $translationManager->getTranslationUnit($id);
            if (!$unit) {
                http_response_code(404); 
                echo json_encode(['error' => 'Translation unit not found']);
                break;
            }

            $success = deleteTranslationUnit($id, $translationManager);

            if ($success) {
                http_response_code(200); 
                echo json_encode([
                    'message' => 'Translation unit deleted successfully'
                ]);
            } else {
                http_response_code(500); 
                echo json_encode(['error' => 'Failed to delete translation unit']);
            }
            break;

        default:
            http_response_code(405); 
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
}

/**
 * Handle translation history resource requests
 */
function handleTranslationHistoryResource($method, $request, $translationManager)
{
    $translationUnitId = $request[0] ?? null;

    if ($method === 'GET' && $translationUnitId) {
        $history = $translationManager->getTranslationHistory($translationUnitId);
        http_response_code(200); 
        echo json_encode($history);
    } else {
        http_response_code(405); 
        echo json_encode(['error' => 'Method not allowed or missing translation unit ID']);
    }
}

/**
 * Get all projects
 */
function getProjects($projectManager)
{
    try {
        $pdo = $projectManager->getPdo();
        $stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching projects: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a specific project
 */
function getProject($id, $projectManager)
{
    try {
        $pdo = $projectManager->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching project: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a project
 */
function updateProject($id, $data, $projectManager)
{
    try {
        $pdo = $projectManager->getPdo();

        $setFields = [];
        $params = [':id' => $id];

        if (isset($data['name'])) {
            $setFields[] = "name = :name";
            $params[':name'] = $data['name'];
        }

        if (isset($data['source_language'])) {
            $setFields[] = "source_language = :source_language";
            $params[':source_language'] = $data['source_language'];
        }

        if (isset($data['target_language'])) {
            $setFields[] = "target_language = :target_language";
            $params[':target_language'] = $data['target_language'];
        }

        if (empty($setFields)) {
            return false; 
        }

        $sql = "UPDATE projects SET " . implode(", ", $setFields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error updating project: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all documents, optionally filtered by project ID
 */
function getDocuments($documentManager, $projectId = null)
{
    try {
        $pdo = $documentManager->getPdo();

        if ($projectId) {
            $stmt = $pdo->prepare("SELECT * FROM documents WHERE project_id = :project_id ORDER BY created_at DESC");
            $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->query("SELECT * FROM documents ORDER BY created_at DESC");
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching documents: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a specific document
 */
function getDocument($id, $documentManager)
{
    try {
        $pdo = $documentManager->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching document: " . $e->getMessage());
        return false;
    }
}

/**
 * Update a document
 */
function updateDocument($id, $data, $documentManager)
{
    try {
        $pdo = $documentManager->getPdo();

        $setFields = [];
        $params = [':id' => $id];

        if (isset($data['name'])) {
            $setFields[] = "name = :name";
            $params[':name'] = $data['name'];
        }

        if (isset($data['path'])) {
            $setFields[] = "path = :path";
            $params[':path'] = $data['path'];
        }

        if (empty($setFields)) {
            return false; 
        }

        $sql = "UPDATE documents SET " . implode(", ", $setFields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error updating document: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all translation units, optionally filtered by document ID
 */
function getTranslationUnits($translationManager, $documentId = null)
{
    try {
        $pdo = $translationManager->getPdo();

        if ($documentId) {
            $stmt = $pdo->prepare("SELECT * FROM translation_units WHERE document_id = :document_id ORDER BY created_at DESC");
            $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->query("SELECT * FROM translation_units ORDER BY created_at DESC LIMIT 100");
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching translation units: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete a translation unit and its history
 */
function deleteTranslationUnit($id, $translationManager)
{
    try {
        $pdo = $translationManager->getPdo();

        // Start transaction
        $pdo->beginTransaction();

        // Delete history records first (due to foreign key constraints)
        $stmt = $pdo->prepare("DELETE FROM translation_history WHERE translation_unit_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Delete the translation unit
        $stmt = $pdo->prepare("DELETE FROM translation_units WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Commit transaction
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error deleting translation unit: " . $e->getMessage());
        return false;
    }
}