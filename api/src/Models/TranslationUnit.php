<?php

namespace App\Models;

use PDO;
use PDOException;
use InvalidArgumentException;
use Exception;

class TranslationUnit {
    private $db;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all translation units with their history
     * 
     * @return array All translation units with history
     */
    public function getAllUnits() {
        try {
            $query = "SELECT * FROM translation_units ORDER BY id DESC";
            $stmt = $this->db->query($query);
            $units = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!is_array($units)) {
                $this->logError('Units data is not an array. Got: ' . gettype($units));
                return [];
            }
            
            // Add history for each unit
            foreach ($units as &$unit) {
                $unit['history'] = $this->getHistoryForUnit($unit['id']);
            }
            
            return $units;
        } catch (PDOException $e) {
            $this->logError('Error fetching all units: ' . $e->getMessage());
            return [];
        } catch (Exception $e) {
            $this->logError('Unexpected error in getAllUnits: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a specific translation unit by ID
     * 
     * @param int $id The unit ID
     * @return array|null The unit or null if not found
     */
    public function getUnit($id) {
        try {
            $query = "SELECT * FROM translation_units WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $unit = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$unit) {
                return null;
            }
            
            // Add history
            $unit['history'] = $this->getHistoryForUnit($id);
            
            return $unit;
        } catch (PDOException $e) {
            $this->logError('Error fetching unit: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new translation unit
     * 
     * @param string $sourceLanguage Source language code
     * @param string $targetLanguage Target language code
     * @param string $sourceText Original text
     * @param string $targetText Translated text
     * @return array|null The created unit or null if failed
     */
    public function createUnit($sourceLanguage, $targetLanguage, $sourceText, $targetText) {
        try {
            // Validate input
            $this->validateInput($sourceLanguage, $targetLanguage, $sourceText, $targetText);
            
            $now = date('Y-m-d H:i:s');
            
            $query = "INSERT INTO translation_units 
                (source_language, target_language, source_text, target_text, created_at, updated_at) 
                VALUES (:source_language, :target_language, :source_text, :target_text, :created_at, :updated_at)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':source_language', $sourceLanguage);
            $stmt->bindParam(':target_language', $targetLanguage);
            $stmt->bindParam(':source_text', $sourceText);
            $stmt->bindParam(':target_text', $targetText);
            $stmt->bindParam(':created_at', $now);
            $stmt->bindParam(':updated_at', $now);
            
            $stmt->execute();
            
            // Get the newly created unit
            $id = $this->db->lastInsertId();
            return $this->getUnit($id);
        } catch (PDOException $e) {
            $this->logError('Error creating unit: ' . $e->getMessage());
            return null;
        } catch (InvalidArgumentException $e) {
            $this->logError('Validation error: ' . $e->getMessage());
            throw $e; // Re-throw for API handling
        }
    }
    
    /**
     * Update a translation unit
     * 
     * @param int $id Unit ID
     * @param string $targetText New translated text
     * @return bool Success status
     */
    public function updateUnit($id, $targetText) {
        try {
            // First, get the current unit to preserve its history
            $currentUnit = $this->getUnit($id);
            if (!$currentUnit) {
                return false;
            }
            
            // Insert the current translation into history
            $this->addHistoryEntry($id, $currentUnit['target_text']);
            
            $now = date('Y-m-d H:i:s');
            
            // Update the unit with new translation
            $query = "UPDATE translation_units SET target_text = :target_text, updated_at = :updated_at WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':target_text', $targetText);
            $stmt->bindParam(':updated_at', $now);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logError('Error updating unit: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a translation unit
     * 
     * @param int $id Unit ID
     * @return bool Success status
     */
    public function deleteUnit($id) {
        try {
            // Start a transaction
            $this->db->beginTransaction();
            
            // First delete the history
            $query = "DELETE FROM translation_history WHERE translation_unit_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Then delete the unit itself
            $query = "DELETE FROM translation_units WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Commit the transaction
            $this->db->commit();
            
            return true;
        } catch (PDOException $e) {
            // Rollback on error
            $this->db->rollBack();
            $this->logError('Error deleting unit: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get history entries for a unit
     * 
     * @param int $unitId Unit ID
     * @return array History entries
     */
    public function getHistoryForUnit($unitId) {
        try {
            $query = "SELECT * FROM translation_history 
                WHERE translation_unit_id = :unit_id 
                ORDER BY updated_at DESC";
                
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError('Error fetching history: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add a history entry for a unit
     * 
     * @param int $unitId Unit ID
     * @param string $targetText Previous target text
     * @return bool Success status
     */
    private function addHistoryEntry($unitId, $targetText) {
        try {
            $now = date('Y-m-d H:i:s');
            $query = "INSERT INTO translation_history 
                (translation_unit_id, target_text, updated_at) 
                VALUES (:unit_id, :target_text, :updated_at)";
                
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
            $stmt->bindParam(':target_text', $targetText);
            $stmt->bindParam(':updated_at', $now);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logError('Error adding history: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate input parameters
     * 
     * @param string $sourceLanguage Source language code
     * @param string $targetLanguage Target language code
     * @param string $sourceText Original text
     * @param string $targetText Translated text
     * @return bool True if valid
     * @throws InvalidArgumentException If validation fails
     */
    private function validateInput($sourceLanguage, $targetLanguage, $sourceText, $targetText) {
        if (empty($sourceLanguage) || strlen($sourceLanguage) > 10) {
            throw new InvalidArgumentException("Source language is required and must be ≤ 10 characters");
        }
        
        if (empty($targetLanguage) || strlen($targetLanguage) > 10) {
            throw new InvalidArgumentException("Target language is required and must be ≤ 10 characters");
        }
        
        if (empty($sourceText)) {
            throw new InvalidArgumentException("Source text is required");
        }
        
        if (empty($targetText)) {
            throw new InvalidArgumentException("Target text is required");
        }
        
        return true;
    }
    
    /**
     * Log error message
     * 
     * @param string $message Error message
     */
    private function logError($message) {
        // In a production system, we'd log to a file or monitoring service
        error_log($message);
    }
} 