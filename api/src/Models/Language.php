<?php

namespace App\Models;

use PDO;
use PDOException;
use InvalidArgumentException;

class Language {
    private $db;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    /**
     * Get all languages
     * 
     * @return array Array of languages
     */
    public function getAllLanguages() {
        try {
            $query = "SELECT * FROM languages ORDER BY name ASC";
            $stmt = $this->db->query($query);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError('Error fetching languages: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a specific language by code
     * 
     * @param string $code Language code
     * @return array|null Language data or null if not found
     */
    public function getLanguage($code) {
        try {
            $query = "SELECT * FROM languages WHERE code = :code";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code);
            $stmt->execute();
            
            $language = $stmt->fetch(PDO::FETCH_ASSOC);
            return $language ?: null;
        } catch (PDOException $e) {
            $this->logError('Error fetching language: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new language
     * 
     * @param string $code Language code
     * @param string $name Language name
     * @return array|null New language or null if failed
     */
    public function createLanguage($code, $name) {
        try {
            // Validate input
            $this->validateLanguage($code, $name);
            
            // Check if language already exists
            if ($this->getLanguage($code)) {
                throw new InvalidArgumentException("Language with code '$code' already exists");
            }
            
            $query = "INSERT INTO languages (code, name) VALUES (:code, :name)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':name', $name);
            
            if ($stmt->execute()) {
                return $this->getLanguage($code);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            $this->logError('Error creating language: ' . $e->getMessage());
            return null;
        } catch (InvalidArgumentException $e) {
            $this->logError('Validation error: ' . $e->getMessage());
            throw $e; // Re-throw for API handling
        }
    }
    
    /**
     * Update a language
     * 
     * @param string $code Language code
     * @param string $name New language name
     * @return array|null Updated language or null if failed
     */
    public function updateLanguage($code, $name) {
        try {
            // Validate input
            if (empty($name)) {
                throw new InvalidArgumentException("Language name is required");
            }
            
            // Check if language exists
            if (!$this->getLanguage($code)) {
                throw new InvalidArgumentException("Language with code '$code' not found");
            }
            
            $query = "UPDATE languages SET name = :name WHERE code = :code";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':code', $code);
            $stmt->bindParam(':name', $name);
            
            if ($stmt->execute()) {
                return $this->getLanguage($code);
            } else {
                return null;
            }
        } catch (PDOException $e) {
            $this->logError('Error updating language: ' . $e->getMessage());
            return null;
        } catch (InvalidArgumentException $e) {
            $this->logError('Validation error: ' . $e->getMessage());
            throw $e; // Re-throw for API handling
        }
    }
    
    /**
     * Check if a language exists
     * 
     * @param string $code Language code
     * @return bool Whether the language exists
     */
    public function languageExists($code) {
        return (bool) $this->getLanguage($code);
    }
    
    /**
     * Validate language input
     * 
     * @param string $code Language code
     * @param string $name Language name
     * @return bool True if valid
     * @throws InvalidArgumentException If validation fails
     */
    private function validateLanguage($code, $name) {
        if (empty($code) || strlen($code) > 10) {
            throw new InvalidArgumentException("Language code is required and must be ≤ 10 characters");
        }
        
        if (empty($name)) {
            throw new InvalidArgumentException("Language name is required");
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