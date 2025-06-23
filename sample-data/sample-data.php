<?php


require_once '../src/ProjectManager.php';
require_once '../src/DocumentManager.php';
require_once '../src/TranslationUnit.php';

try {
    // Create managers
    $projectManager = new ProjectManager();
    $documentManager = new DocumentManager();
    $translationManager = new TranslationUnit();

    // Sample data arrays
    $projects = [
        ["Sample Project 1", "en", "es"],
        ["Sample Project 2", "fr", "de"],
        ["Sample Project 3", "it", "pt"]
    ];

    $sampleUsers = [
        ["user1", "user1@example.com", "translator"],
        ["user2", "user2@example.com", "reviewer"],
        ["user3", "user3@example.com", "admin"]
    ];

    $sampleTranslations = [
        ["Hello", "Hola"],
        ["Goodbye", "Adiós"]
    ];

    $pdo = $projectManager->getPdo();

    foreach ($projects as $index => $projectData) {
        [$name, $srcLang, $tgtLang] = $projectData;

        // Create project
        $projectId = $projectManager->createProject($name, $srcLang, $tgtLang);
        if (!$projectId) {
            throw new Exception("Failed to create project: $name");
        }
        echo "Created project ($name) with ID: $projectId\n";

        // Create document
        $docName = "Doc for $name";
        $documentId = $documentManager->createDocument($projectId, $docName, "/dummy/path/$index.txt");
        if (!$documentId) {
            throw new Exception("Failed to create document for $name");
        }
        echo "  Created document ($docName) with ID: $documentId\n";

        // Create user
        [$username, $email, $role] = $sampleUsers[$index];
        $stmt = $pdo->prepare("INSERT INTO users (username, email, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $role]);
        $userId = $pdo->lastInsertId();
        echo "  Created user ($username) with ID: $userId\n";

        // Add translation units
        foreach ($sampleTranslations as [$source, $target]) {
            $unitId = $translationManager->addTranslationUnit($documentId, $source, $target);
            if (!$unitId) {
                throw new Exception("Failed to create translation unit ($source)");
            }
            echo "    Created translation unit ($source → $target) with ID: $unitId\n";
        }

        echo "\n";
    }

    echo "✅ All sample data inserted successfully.\n";
} catch (Exception $e) {
    echo " Error: " . $e->getMessage() . "\n";
}
