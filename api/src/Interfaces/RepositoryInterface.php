<?php

namespace App\Interfaces;

/**
 * Generic repository interface for basic CRUD operations
 */
interface RepositoryInterface
{
    /**
     * Get all records
     * 
     * @return array
     */
    public function getAll(): array;
    
    /**
     * Get a record by ID
     * 
     * @param int|string $id
     * @return array|null
     */
    public function getById($id): ?array;
    
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