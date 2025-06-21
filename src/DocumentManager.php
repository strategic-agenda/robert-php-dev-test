<?php
// filepath: DocumentManager.php

class DocumentManager {
    private $pdo;
    
    /**
     * Constructor - initialize database connection
     */
    public function __construct($host = 'localhost', $dbname = 'translations', $username = 'root', $password = '') {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Create a new document
     */
    public function createDocument($projectId, $name, $path) {
        try {
            // Check if project exists first
            $checkStmt = $this->pdo->prepare("SELECT id FROM projects WHERE id = :id");
            $checkStmt->bindParam(':id', $projectId, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() == 0) {
                throw new Exception("Project with ID $projectId does not exist");
            }
            
            $stmt = $this->pdo->prepare(
                "INSERT INTO documents (project_id, name, path) 
                 VALUES (:project_id, :name, :path)"
            );
            
            $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':path', $path, PDO::PARAM_STR);
            
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating document: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a document exists
     */
    public function documentExists($documentId) {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM documents WHERE id = :id");
            $stmt->bindParam(':id', $documentId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking document existence: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a document and all related data
     * (translation units and history)
     */
    public function deleteDocument($documentId) {
        try {
            // Start transaction to ensure data consistency
            $this->pdo->beginTransaction();
            
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
            
            // Finally delete the document
            $deleteDocStmt = $this->pdo->prepare("DELETE FROM documents WHERE id = :id");
            $deleteDocStmt->bindParam(':id', $documentId, PDO::PARAM_INT);
            $deleteDocStmt->execute();
            
            // Commit transaction
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Roll back transaction on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error deleting document: " . $e->getMessage());
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