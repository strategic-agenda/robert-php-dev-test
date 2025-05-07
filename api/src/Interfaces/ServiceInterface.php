<?php

namespace App\Interfaces;

/**
 * Generic service interface
 */
interface ServiceInterface
{
    /**
     * Get all records
     * 
     * @return array
     */
    public function getAll(): array;
    
    /**
     * Get a specific record
     * 
     * @param int|string $id
     * @return array|null
     */
    public function get($id): ?array;
    
    /**
     * Create a new record
     * 
     * @param array $data
     * @return array|null
     */
    public function create(array $data): ?array;
    
    /**
     * Update a record
     * 
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool;
    
    /**
     * Delete a record
     * 
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool;
} 