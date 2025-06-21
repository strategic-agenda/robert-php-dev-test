<?php


require_once '../src/ProjectManager.php';
require_once '../src/DocumentManager.php';
require_once '../src/TranslationUnit.php';

try {
    // Create managers
    $projectManager = new ProjectManager();
    $documentManager = new DocumentManager();
    $translationManager = new TranslationUnit();

    // Create a project
    $projectId = $projectManager->createProject("Sample Project", "en", "es");
    if (!$projectId) {
        throw new Exception("Failed to create project");
    }
    echo "Created project with ID: $projectId\n";

    // Create a document
    $documentId = $documentManager->createDocument($projectId, "Sample Document", "/path/to/document");
    if (!$documentId) {
        throw new Exception("Failed to create document");
    }
    echo "Created document with ID: $documentId\n";

    // Create a user (this would typically be in a UserManager class)
    $pdo = $projectManager->getPdo();
    $stmt = $pdo->prepare("INSERT INTO users (username, email, role) VALUES (?, ?, ?)");
    $stmt->execute(["testuser", "test@example.com", "translator"]);
    $userId = $pdo->lastInsertId();
    echo "Created user with ID: $userId\n";

    // Create a translation unit
    $unitId = $translationManager->addTranslationUnit($documentId, "Hello world");
    if (!$unitId) {
        throw new Exception("Failed to create translation unit");
    }
    echo "Created translation unit with ID: $unitId\n";

    echo "\nSample data created successfully. You can now use:\n";
    echo "Project ID: $projectId\n";
    echo "Document ID: $documentId\n";
    echo "User ID: $userId\n";
    echo "Translation Unit ID: $unitId\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
