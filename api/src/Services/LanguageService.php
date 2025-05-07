<?php

namespace App\Services;

use App\Interfaces\LanguageRepositoryInterface;
use App\Interfaces\LanguageServiceInterface;
use InvalidArgumentException;

/**
 * Service for managing languages
 */
class LanguageService implements LanguageServiceInterface
{
    /**
     * @var LanguageRepositoryInterface
     */
    private $repository;
    
    /**
     * Constructor
     * 
     * @param LanguageRepositoryInterface $repository
     */
    public function __construct(LanguageRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * Get all languages
     * 
     * @return array
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }
    
    /**
     * Get a specific language
     * 
     * @param int|string $id
     * @return array|null
     */
    public function get($id): ?array
    {
        return $this->repository->getById($id);
    }
    
    /**
     * Get a language by its code
     * 
     * @param string $code
     * @return array|null
     */
    public function getByCode(string $code): ?array
    {
        return $this->repository->getByCode($code);
    }
    
    /**
     * Create a new language
     * 
     * @param array $data
     * @return array|null
     * @throws InvalidArgumentException
     */
    public function create(array $data): ?array
    {
        // Validate language data
        if (empty($data['code']) || empty($data['name'])) {
            throw new InvalidArgumentException('Language code and name are required');
        }
        
        $this->validateLanguage($data['code'], $data['name']);
        
        // Check if language already exists
        if ($this->repository->languageExists($data['code'])) {
            throw new InvalidArgumentException("Language with code '{$data['code']}' already exists");
        }
        
        return $this->repository->create($data);
    }
    
    /**
     * Update a language
     * 
     * @param int|string $id
     * @param array $data
     * @return bool
     * @throws InvalidArgumentException
     */
    public function update($id, array $data): bool
    {
        // Validate language name
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Language name is required');
        }
        
        // Get the language to validate
        $language = $this->repository->getById($id);
        if (!$language) {
            throw new InvalidArgumentException("Language with ID '$id' not found");
        }
        
        $this->validateLanguage($language['code'], $data['name']);
        
        return $this->repository->update($id, $data);
    }
    
    /**
     * Delete a language
     * 
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool
    {
        return $this->repository->delete($id);
    }
    
    /**
     * Validate language data
     * 
     * @param string $code
     * @param string $name
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validateLanguage(string $code, string $name): bool
    {
        if (empty($code) || strlen($code) > 10) {
            throw new InvalidArgumentException("Language code is required and must be ≤ 10 characters");
        }
        
        if (empty($name)) {
            throw new InvalidArgumentException("Language name is required");
        }
        
        if (strlen($name) > 50) {
            throw new InvalidArgumentException("Language name must be ≤ 50 characters");
        }
        
        return true;
    }
} 