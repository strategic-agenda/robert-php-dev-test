<?php

namespace App\Repositories;

use App\Interfaces\LanguageRepositoryInterface;
use PDO;
use PDOException;

/**
 * Repository for languages
 */
class LanguageRepository implements LanguageRepositoryInterface
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
     * Get all languages
     * 
     * @return array
     */
    public function getAll(): array
    {
        try {
            $query = "SELECT * FROM languages ORDER BY name";
            $stmt = $this->db->query($query);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError('Error fetching languages: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a language by ID
     * 
     * @param int|string $id Language ID
     * @return array|null
     */
    public function getById($id): ?array
    {
        try {
            $query = "SELECT * FROM languages WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $language = $stmt->fetch(PDO::FETCH_ASSOC);
            return $language ?: null;
        } catch (PDOException $e) {
            $this->logError('Error fetching language by ID: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get a language by code
     * 
     * @param string $code Language code
     * @return array|null
     */
    public function getByCode(string $code): ?array
    {
        try {
            $query = "SELECT * FROM languages WHERE code = :code";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();
            
            $language = $stmt->fetch(PDO::FETCH_ASSOC);
            return $language ?: null;
        } catch (PDOException $e) {
            $this->logError('Error fetching language by code: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new language
     * 
     * @param array $data Language data
     * @return array|null
     */
    public function create(array $data): ?array
    {
        try {
            $query = "INSERT INTO languages (code, name) VALUES (:code, :name)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $data['code'], PDO::PARAM_STR);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->execute();
            
            return $this->getByCode($data['code']);
        } catch (PDOException $e) {
            $this->logError('Error creating language: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update a language
     * 
     * @param int|string $id Language ID
     * @param array $data Language data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        try {
            $query = "UPDATE languages SET name = :name WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logError('Error updating language: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a language
     * 
     * @param int|string $id Language ID
     * @return bool
     */
    public function delete($id): bool
    {
        try {
            $query = "DELETE FROM languages WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logError('Error deleting language: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a language exists
     * 
     * @param string $code Language code
     * @return bool
     */
    public function languageExists(string $code): bool
    {
        try {
            $query = "SELECT COUNT(*) FROM languages WHERE code = :code";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code, PDO::PARAM_STR);
            $stmt->execute();
            
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->logError('Error checking if language exists: ' . $e->getMessage());
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