<?php

namespace App\Interfaces;

/**
 * Repository interface for languages
 */
interface LanguageRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a language by its code
     * 
     * @param string $code
     * @return array|null
     */
    public function getByCode(string $code): ?array;
    
    /**
     * Check if a language exists
     * 
     * @param string $code
     * @return bool
     */
    public function languageExists(string $code): bool;
} 