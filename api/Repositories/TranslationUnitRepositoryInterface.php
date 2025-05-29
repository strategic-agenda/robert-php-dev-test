<?php

declare(strict_types=1);

namespace Api\Repositories;

use App\Models\TranslationUnit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * TranslationUnitRepositoryInterface
 * 
 * Interface defining the contract for translation unit data access.
 * Specifies the required methods for translation unit persistence operations.
 */
interface TranslationUnitRepositoryInterface
{
    /**
     * Get all translation units with optional filtering and pagination.
     *
     * @param array $filters Optional filters for the query
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated translation units
     */
    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Find a translation unit by its ID.
     *
     * @param string $id The translation unit ID
     * @return TranslationUnit|null The found translation unit or null
     */
    public function findById(string $id): ?TranslationUnit;

    /**
     * Create a new translation unit.
     *
     * @param array $data The translation unit data
     * @return TranslationUnit The created translation unit
     */
    public function create(array $data): TranslationUnit;

    /**
     * Update an existing translation unit.
     *
     * @param string $id The translation unit ID
     * @param array $data The update data
     * @return TranslationUnit The updated translation unit
     */
    public function update(string $id, array $data): TranslationUnit;

    /**
     * Delete a translation unit.
     *
     * @param string $id The translation unit ID
     * @return bool True if deletion was successful
     */
    public function delete(string $id): bool;

    /**
     * Get the history of changes for a translation unit.
     *
     * @param string $id The translation unit ID
     * @return Collection The translation history
     */
    public function getHistory(string $id): Collection;
} 