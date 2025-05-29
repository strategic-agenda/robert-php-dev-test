<?php

declare(strict_types=1);

namespace Api\Repositories;

use App\Models\TranslationUnit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * TranslationUnitRepository
 * 
 * Repository class for handling translation unit data persistence.
 * Implements the TranslationUnitRepositoryInterface for database operations.
 */
class TranslationUnitRepository implements TranslationUnitRepositoryInterface
{
    /**
     * Create a new repository instance.
     *
     * @param TranslationUnit $model The translation unit model
     */
    public function __construct(
        private readonly TranslationUnit $model
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
        $query = $this->model->query();

        if (isset($filters['source_language'])) {
            $query->where('source_language', $filters['source_language']);
        }
        if (isset($filters['target_language'])) {
            $query->where('target_language', $filters['target_language']);
        }
        if (isset($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        return $query->with('history')->paginate($perPage);
    }

    /**
     * Find a translation unit by its ID.
     *
     * @param string $id The translation unit ID
     * @return TranslationUnit|null The found translation unit or null
     */
    public function findById(string $id): ?TranslationUnit
    {
        return $this->model->with('history')->find($id);
    }

    /**
     * Create a new translation unit.
     *
     * @param array $data The translation unit data
     * @return TranslationUnit The created translation unit
     */
    public function create(array $data): TranslationUnit
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing translation unit.
     *
     * @param string $id The translation unit ID
     * @param array $data The update data
     * @return TranslationUnit The updated translation unit
     * @throws InvalidArgumentException If translation unit is not found
     */
    public function update(string $id, array $data): TranslationUnit
    {
        $translationUnit = $this->findById($id);
        
        if (!$translationUnit) {
            throw new \InvalidArgumentException('Translation unit not found');
        }

        $translationUnit->updateTargetText(
            $data['target_text'],
            $data['changed_by'],
            $data['change_reason'] ?? null
        );

        return $translationUnit;
    }

    /**
     * Delete a translation unit.
     *
     * @param string $id The translation unit ID
     * @return bool True if deletion was successful
     * @throws InvalidArgumentException If translation unit is not found
     */
    public function delete(string $id): bool
    {
        $translationUnit = $this->findById($id);
        
        if (!$translationUnit) {
            throw new \InvalidArgumentException('Translation unit not found');
        }

        return $translationUnit->delete();
    }

    /**
     * Get the history of changes for a translation unit.
     *
     * @param string $id The translation unit ID
     * @return Collection The translation history
     * @throws InvalidArgumentException If translation unit is not found
     */
    public function getHistory(string $id): Collection
    {
        $translationUnit = $this->findById($id);
        
        if (!$translationUnit) {
            throw new \InvalidArgumentException('Translation unit not found');
        }

        return $translationUnit->history;
    }
} 