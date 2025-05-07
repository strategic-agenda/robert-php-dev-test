<?php

namespace App\Repositories;

use App\Interfaces\TranslationUnitRepositoryInterface;
use PDO;
use PDOException;

/**
 * Repository for translation units
 */
class TranslationUnitRepository implements TranslationUnitRepositoryInterface
{
    /**
     * @var PDO Database connection
     */
    private $db;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Get all translation units
     * 
     * @return array
     */
    public function getAll(): array
    {
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
        } catch (\Exception $e) {
            $this->logError('Unexpected error in getAll: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a unit by ID
     * 
     * @param int|string $id Unit ID
     * @return array|null
     */
    public function getById($id): ?array
    {
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
     * @param array $data Unit data
     * @return array|null
     */
    public function create(array $data): ?array
    {
        try {
            $now = date('Y-m-d H:i:s');
            
            $query = "INSERT INTO translation_units 
                (source_language, target_language, source_text, target_text, created_at, updated_at) 
                VALUES (:source_language, :target_language, :source_text, :target_text, :created_at, :updated_at)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':source_language', $data['source_language']);
            $stmt->bindParam(':target_language', $data['target_language']);
            $stmt->bindParam(':source_text', $data['source_text']);
            $stmt->bindParam(':target_text', $data['target_text']);
            $stmt->bindParam(':created_at', $now);
            $stmt->bindParam(':updated_at', $now);
            
            $stmt->execute();
            
            // Get the newly created unit
            $id = $this->db->lastInsertId();
            return $this->getById($id);
        } catch (PDOException $e) {
            $this->logError('Error creating unit: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update a translation unit
     * 
     * @param int|string $id Unit ID
     * @param array $data Unit data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        try {
            // First, get the current unit to preserve its history
            $currentUnit = $this->getById($id);
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
            $stmt->bindParam(':target_text', $data['target_text']);
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
     * @param int|string $id Unit ID
     * @return bool
     */
    public function delete($id): bool
    {
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
     * @return array
     */
    public function getHistoryForUnit(int $unitId): array
    {
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
     * @return bool
     */
    public function addHistoryEntry(int $unitId, string $targetText): bool
    {
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
     * Log error message
     * 
     * @param string $message Error message
     */
    private function logError(string $message): void
    {
        // In a production system, we'd log to a file or monitoring service
        error_log($message);
    }
} 