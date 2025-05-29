<?php

declare(strict_types=1);

namespace Api\Repositories;

use App\Models\TranslationUnit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TranslationUnitRepository implements TranslationUnitRepositoryInterface
{
    public function __construct(
        private readonly TranslationUnit $model
    ) {}

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

    public function findById(string $id): ?TranslationUnit
    {
        return $this->model->with('history')->find($id);
    }

    public function create(array $data): TranslationUnit
    {
        return $this->model->create($data);
    }

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

    public function delete(string $id): bool
    {
        $translationUnit = $this->findById($id);
        
        if (!$translationUnit) {
            throw new \InvalidArgumentException('Translation unit not found');
        }

        return $translationUnit->delete();
    }

    public function getHistory(string $id): Collection
    {
        $translationUnit = $this->findById($id);
        
        if (!$translationUnit) {
            throw new \InvalidArgumentException('Translation unit not found');
        }

        return $translationUnit->history;
    }
} 