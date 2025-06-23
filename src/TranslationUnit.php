<?php

namespace App;

use PDO;
use PDOException;
use Exception;
class TranslationUnit
{
    // Implement the class as per the tasks.
    protected $pdo;
    
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
     * Add a new translation unit
     * 
     * @param int $documentId The document ID this unit belongs to
     * @param string $sourceText The source text to translate
     * @param string|null $targetText The translated text (can be null initially)
     * @param string $status The status of translation (e.g., 'new', 'in-progress', 'completed')
     * @return int|bool The ID of the newly created unit or false on failure
     */
    public function addTranslationUnit($documentId, $sourceText, $targetText = null, $status = 'new') {
        try {


            $stmt = $this->pdo->prepare(
                "INSERT INTO translation_units (document_id, source_text, target_text, status) 
                 VALUES (:document_id, :source_text, :target_text, :status)"
            );
            
            $stmt->bindParam(':document_id', $documentId, PDO::PARAM_INT);
            $stmt->bindParam(':source_text', $sourceText, PDO::PARAM_STR);
            $stmt->bindParam(':target_text', $targetText, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            
            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding translation unit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve a translation unit by ID
     * 
     * @param int $id The ID of the translation unit to retrieve
     * @return array|bool The translation unit data or false if not found
     */
    public function getTranslationUnit($id) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM translation_units WHERE id = :id"
            );
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: false;
        } catch (PDOException $e) {
            error_log("Error retrieving translation unit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update a translation unit and keep history
     * 
     * @param int $id The ID of the translation unit to update
     * @param string $newTargetText The new translated text
     * @param int $userId The ID of the user making the change
     * @param string $status Optional new status
     * @return bool True on success, false on failure
     */
    public function updateTranslationUnit($id, $newTargetText, $userId, $status = null) {
        try {
            // Start transaction to ensure data consistency
            $this->pdo->beginTransaction();
            
            // Get current translation unit data
            $currentUnit = $this->getTranslationUnit($id);
            if (!$currentUnit) {
                throw new Exception("Translation unit with ID $id not found");
            }
            
            // Update the translation unit
            $updateSql = "UPDATE translation_units SET 
                          target_text = :target_text, 
                          version = version + 1";
            
            // Add status update if provided
            if ($status !== null) {
                $updateSql .= ", status = :status";
            }
            
            $updateSql .= " WHERE id = :id";
            
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->bindParam(':target_text', $newTargetText, PDO::PARAM_STR);
            $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($status !== null) {
                $updateStmt->bindParam(':status', $status, PDO::PARAM_STR);
            }
            
            $updateStmt->execute();
            
            // Add history record
            $historyStmt = $this->pdo->prepare(
                "INSERT INTO translation_history 
                (translation_unit_id, user_id, old_target_text, new_target_text) 
                VALUES (:translation_unit_id, :user_id, :old_target_text, :new_target_text)"
            );
            
            $historyStmt->bindParam(':translation_unit_id', $id, PDO::PARAM_INT);
            $historyStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $historyStmt->bindParam(':old_target_text', $currentUnit['target_text'], PDO::PARAM_STR);
            $historyStmt->bindParam(':new_target_text', $newTargetText, PDO::PARAM_STR);
            
            $historyStmt->execute();
            
            // Commit transaction
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Roll back transaction on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error updating translation unit: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the translation history for a unit
     * 
     * @param int $translationUnitId The translation unit ID
     * @return array The history records
     */
    public function getTranslationHistory($translationUnitId) {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT th.*, u.username 
                 FROM translation_history th
                 JOIN users u ON th.user_id = u.id
                 WHERE translation_unit_id = :translation_unit_id
                 ORDER BY changed_at DESC"
            );
            
            $stmt->bindParam(':translation_unit_id', $translationUnitId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error retrieving translation history: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Get the PDO instance (for use in the API)
     * 
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }
}
