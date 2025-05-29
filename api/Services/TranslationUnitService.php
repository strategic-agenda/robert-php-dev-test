<?php

declare(strict_types=1);

namespace Api\Services;

use Api\Repositories\TranslationUnitRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TranslationUnitService
{
    public function __construct(
        private readonly TranslationUnitRepositoryInterface $repository
    ) {}

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getAll($filters, $perPage);
    }

    public function findById(string $id)
    {
        $translationUnit = $this->repository->findById($id);
        
        if (!$translationUnit) {
            throw new \InvalidArgumentException('Translation unit not found');
        }

        return $translationUnit;
    }

    public function create(array $data)
    {
        $this->validateCreateData($data);
        return $this->repository->create($data);
    }

    public function update(string $id, array $data)
    {
        $this->validateUpdateData($data);
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getHistory(string $id): Collection
    {
        return $this->repository->getHistory($id);
    }

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