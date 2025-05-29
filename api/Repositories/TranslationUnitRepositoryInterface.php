<?php

declare(strict_types=1);

namespace Api\Repositories;

use App\Models\TranslationUnit;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TranslationUnitRepositoryInterface
{
    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function findById(string $id): ?TranslationUnit;
    public function create(array $data): TranslationUnit;
    public function update(string $id, array $data): TranslationUnit;
    public function delete(string $id): bool;
    public function getHistory(string $id): Collection;
} 