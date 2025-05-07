<?php

namespace App\Services;

use App\Interfaces\TranslationUnitRepositoryInterface;
use App\Interfaces\TranslationUnitServiceInterface;
use InvalidArgumentException;

/**
 * Service for managing translation units
 */
class TranslationUnitService implements TranslationUnitServiceInterface
{
    /**
     * @var TranslationUnitRepositoryInterface
     */
    private $repository;
    
    /**
     * Constructor
     * 
     * @param TranslationUnitRepositoryInterface $repository
     */
    public function __construct(TranslationUnitRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * Get all translation units
     * 
     * @return array
     */
    public function getAll(): array
    {
        return $this->repository->getAll();
    }
    
    /**
     * Get a specific translation unit
     * 
     * @param int|string $id
     * @return array|null
     */
    public function get($id): ?array
    {
        return $this->repository->getById($id);
    }
    
    /**
     * Create a new translation unit
     * 
     * @param array $data
     * @return array|null
     * @throws InvalidArgumentException
     */
    public function create(array $data): ?array
    {
        // Validate input
        $this->validateUnitData($data);
        
        // Map camelCase to snake_case
        $dbData = [
            'source_language' => $data['sourceLanguage'] ?? $data['source_language'] ?? null,
            'target_language' => $data['targetLanguage'] ?? $data['target_language'] ?? null,
            'source_text' => $data['sourceText'] ?? $data['source_text'] ?? null,
            'target_text' => $data['targetText'] ?? $data['target_text'] ?? null
        ];
        
        $result = $this->repository->create($dbData);
        
        // Map snake_case back to camelCase for frontend
        if ($result) {
            $result = $this->mapToCamelCase($result);
        }
        
        return $result;
    }
    
    /**
     * Update a translation unit
     * 
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        // Map camelCase to snake_case
        $dbData = [
            'target_text' => $data['targetText'] ?? $data['target_text'] ?? null
        ];
        
        return $this->repository->update($id, $dbData);
    }
    
    /**
     * Update a translation with history tracking
     * 
     * @param int $id
     * @param string $targetText
     * @return array|null
     */
    public function updateTranslation(int $id, string $targetText): ?array
    {
        // Prepare data
        $data = ['target_text' => $targetText];
        
        // Update translation
        $success = $this->repository->update($id, $data);
        if (!$success) {
            return null;
        }
        
        // Return updated unit
        $unit = $this->repository->getById($id);
        return $unit ? $this->mapToCamelCase($unit) : null;
    }
    
    /**
     * Delete a translation unit
     * 
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool
    {
        return $this->repository->delete($id);
    }
    
    /**
     * Get the translation history for a unit
     * 
     * @param int $unitId
     * @return array
     */
    public function getHistory(int $unitId): array
    {
        return $this->repository->getHistoryForUnit($unitId);
    }
    
    /**
     * Validate a translation unit's data
     * 
     * @param array $data
     * @param bool $requireAll
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validateUnitData(array $data, bool $requireAll = true): bool
    {
        $required = ['sourceText', 'targetText'];
        
        if ($requireAll) {
            $required = array_merge($required, ['sourceLanguage', 'targetLanguage']);
        }
        
        // Check for required fields using both camelCase and snake_case
        foreach ($required as $field) {
            $snakeField = $this->camelToSnake($field);
            
            if (empty($data[$field]) && empty($data[$snakeField])) {
                throw new InvalidArgumentException("Field '$field' is required");
            }
        }
        
        // Validate field lengths and formats here if needed
        
        return true;
    }
    
    /**
     * Convert camelCase to snake_case
     * 
     * @param string $string
     * @return string
     */
    private function camelToSnake(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
    
    /**
     * Map database fields (snake_case) to frontend fields (camelCase)
     * 
     * @param array $data
     * @return array
     */
    private function mapToCamelCase(array $data): array
    {
        $result = [];
        $result['id'] = $data['id'] ?? null;
        $result['sourceLanguage'] = $data['source_language'] ?? null;
        $result['targetLanguage'] = $data['target_language'] ?? null;
        $result['sourceText'] = $data['source_text'] ?? null;
        $result['targetText'] = $data['target_text'] ?? null;
        $result['createdAt'] = $data['created_at'] ?? null;
        $result['updatedAt'] = $data['updated_at'] ?? null;
        
        // Also include the original fields for backward compatibility
        $result['source_language'] = $data['source_language'] ?? null;
        $result['target_language'] = $data['target_language'] ?? null;
        $result['source_text'] = $data['source_text'] ?? null;
        $result['target_text'] = $data['target_text'] ?? null;
        $result['created_at'] = $data['created_at'] ?? null;
        $result['updated_at'] = $data['updated_at'] ?? null;
        
        // Map history if it exists
        if (isset($data['history'])) {
            $result['history'] = $data['history'];
        } else {
            $result['history'] = [];
        }
        
        return $result;
    }
} 