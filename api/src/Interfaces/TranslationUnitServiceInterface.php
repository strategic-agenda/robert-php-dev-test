<?php

namespace App\Interfaces;

/**
 * Service interface for managing translation units
 */
interface TranslationUnitServiceInterface extends ServiceInterface
{
    /**
     * Get the translation history for a unit
     * 
     * @param int $unitId
     * @return array
     */
    public function getHistory(int $unitId): array;
    
    /**
     * Update a translation with history tracking
     * 
     * @param int $id
     * @param string $targetText
     * @return array|null
     */
    public function updateTranslation(int $id, string $targetText): ?array;
    
    /**
     * Validate a translation unit's data
     * 
     * @param array $data
     * @param bool $requireAll
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateUnitData(array $data, bool $requireAll = true): bool;
} 