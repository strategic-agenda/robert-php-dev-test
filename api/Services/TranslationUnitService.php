<?php

declare(strict_types=1);

namespace Api\Services;

use Api\Repositories\TranslationUnitRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * TranslationUnitService
 * 
 * Service class for handling translation unit business logic.
 * Provides methods for managing translation units and their history.
 */
class TranslationUnitService
{
    /**
     * Create a new service instance.
     *
     * @param TranslationUnitRepositoryInterface $repository The translation unit repository
     */
    public function __construct(
        private readonly TranslationUnitRepositoryInterface $repository
    ) {}

    /**
     * Get all translation units with optional filtering and pagination.
     *
     * @param array $filters Optional filters for the query
     * @param int $perPage Number of items per page
     * @return LengthAwarePaginator Paginated translation units
     */
    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    /**
     * Find a translation unit by its ID.
     *
     * @param string $id The translation unit ID
     * @return TranslationUnit The found translation unit
     * @throws InvalidArgumentException If translation unit is not found
     */
    public function findById(string $id)
    {
        $translationUnit = $this->repository->findById($id);
        
        if (!$translationUnit) {
            throw new \InvalidArgumentException('Translation unit not found');
        }

        return $translationUnit;
    }

    /**
     * Create a new translation unit.
     *
     * @param array $data The translation unit data
     * @return TranslationUnit The created translation unit
     * @throws ValidationException If validation fails
     */
    public function create(array $data)
    {
        $this->validateCreateData($data);
        return $this->repository->create($data);
    }

    /**
     * Update an existing translation unit.
     *
     * @param string $id The translation unit ID
     * @param array $data The update data
     * @return TranslationUnit The updated translation unit
     * @throws ValidationException If validation fails
     */
    public function update(string $id, array $data)
    {
        $this->validateUpdateData($data);
        return $this->repository->update($id, $data);
    }

    /**
     * Delete a translation unit.
     *
     * @param string $id The translation unit ID
     * @return bool True if deletion was successful
     */
    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Get the history of changes for a translation unit.
     *
     * @param string $id The translation unit ID
     * @return Collection The translation history
     */
    public function getHistory(string $id): Collection
    {
        return $this->repository->getHistory($id);
    }

    /**
     * Validate the data for creating a translation unit.
     *
     * @param array $data The data to validate
     * @throws ValidationException If validation fails
     */
    private function validateCreateData(array $data): void
    {
        $validator = Validator::make($data, [
            'source_text' => 'required|string',
            'source_language' => 'required|string|regex:/^[a-z]{2}(-[A-Z]{2})?$/',
            'target_language' => 'required|string|regex:/^[a-z]{2}(-[A-Z]{2})?$/',
            'project_id' => 'required|string',
            'target_text' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Validate the data for updating a translation unit.
     *
     * @param array $data The data to validate
     * @throws ValidationException If validation fails
     */
    private function validateUpdateData(array $data): void
    {
        $validator = Validator::make($data, [
            'target_text' => 'required|string',
            'changed_by' => 'required|string',
            'change_reason' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
} 