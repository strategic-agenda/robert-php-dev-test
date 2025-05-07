<?php

namespace App\Interfaces;

/**
 * Service interface for managing languages
 */
interface LanguageServiceInterface extends ServiceInterface
{
    /**
     * Get a language by its code
     * 
     * @param string $code
     * @return array|null
     */
    public function getByCode(string $code): ?array;
    
    /**
     * Validate language data
     * 
     * @param string $code
     * @param string $name
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validateLanguage(string $code, string $name): bool;
} 