<?php

namespace App;

use PDO;
use PDOException;
use Exception;
class ProjectManager {
    private $pdo;
    
    /**
     * Constructor - initialize database connection
     */
    public function __construct($host = '127.0.0.1', $dbname = 'translations', $username = 'root', $password = '') {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new project
     */
    public function createProject($name, $sourceLanguage, $targetLanguage) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO projects (name, source_language, target_language) 
                 VALUES (:name, :source_language, :target_language)"
            );
            
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':source_language', $sourceLanguage, PDO::PARAM_STR);
            $stmt->bindParam(':target_language', $targetLanguage, PDO::PARAM_STR);
            
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating project: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a project exists
     */
    public function projectExists($projectId) {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM projects WHERE id = :id");
            $stmt->bindParam(':id', $projectId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking project existence: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a project and all related data
     * (documents, translation units and history)
     */
    public function deleteProject($projectId) {
        try {
            // Start transaction to ensure data consistency
            $this->pdo->beginTransaction();
            
            // Get all documents in this project
            $docStmt = $this->pdo->prepare("SELECT id FROM documents WHERE project_id = :project_id");
            $docStmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $docStmt->execute();
            $documents = $docStmt->fetchAll(PDO::FETCH_COLUMN);
            
            // For each document, delete translation units and history
            foreach ($documents as $documentId) {
                // Get all translation units for this document
                $unitStmt = $this->pdo->prepare("SELECT id FROM translation_units WHERE document_id = :document_id");
                $unitStmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
                $unitStmt->execute();
                $units = $unitStmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Delete translation history for each unit
                foreach ($units as $unitId) {
                    $historyStmt = $this->pdo->prepare("DELETE FROM translation_history WHERE translation_unit_id = :unit_id");
                    $historyStmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
                    $historyStmt->execute();
                }
                
                // Delete translation units for this document
                $deleteUnitsStmt = $this->pdo->prepare("DELETE FROM translation_units WHERE document_id = :document_id");
                $deleteUnitsStmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
                $deleteUnitsStmt->execute();
            }
            
            // Delete documents for this project
            $deleteDocsStmt = $this->pdo->prepare("DELETE FROM documents WHERE project_id = :project_id");
            $deleteDocsStmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $deleteDocsStmt->execute();
            
            // Finally delete the project
            $deleteProjectStmt = $this->pdo->prepare("DELETE FROM projects WHERE id = :id");
            $deleteProjectStmt->bindParam(':id', $projectId, PDO::PARAM_INT);
            $deleteProjectStmt->execute();
            
            // Commit transaction
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Roll back transaction on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error deleting project: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the PDO instance
     */
    public function getPdo() {
        return $this->pdo;
    }
}