<?php

namespace App\Interfaces;

/**
 * Repository interface for translation units
 */
interface TranslationUnitRepositoryInterface extends RepositoryInterface
{
    /**
     * Get the history for a specific translation unit
     * 
     * @param int $unitId
     * @return array
     */
    public function getHistoryForUnit(int $unitId): array;
    
    /**
     * Add a history entry for a unit
     * 
     * @param int $unitId
     * @param string $targetText
     * @return bool
     */
    public function addHistoryEntry(int $unitId, string $targetText): bool;
} 