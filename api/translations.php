<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\TranslationUnit;
use InvalidArgumentException;

class TranslationUnitController
{
    private array $translationUnits = [];

    public function list(array $queryParams = []): array
    {
        $page = (int)($queryParams['page'] ?? 1);
        $perPage = (int)($queryParams['per_page'] ?? 10);
        $sourceLanguage = $queryParams['source_language'] ?? null;
        $targetLanguage = $queryParams['target_language'] ?? null;

        $filteredUnits = array_filter($this->translationUnits, function (TranslationUnit $unit) use ($sourceLanguage, $targetLanguage) {
            if ($sourceLanguage && $unit->getSourceLanguage() !== $sourceLanguage) {
                return false;
            }
            if ($targetLanguage && $unit->getTargetLanguage() !== $targetLanguage) {
                return false;
            }
            return true;
        });

        $total = count($filteredUnits);
        $offset = ($page - 1) * $perPage;
        $units = array_slice($filteredUnits, $offset, $perPage);

        return [
            'data' => array_map(fn($unit) => $unit->toArray(), $units),
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }

    public function get(string $id): array
    {
        if (!isset($this->translationUnits[$id])) {
            throw new InvalidArgumentException('Translation unit not found');
        }

        return $this->translationUnits[$id]->toArray();
    }

    public function create(array $data): array
    {
        $requiredFields = ['source_text', 'source_language', 'target_language', 'project_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field: {$field}");
            }
        }

        $unit = new TranslationUnit(
            $data['source_text'],
            $data['source_language'],
            $data['target_language'],
            $data['project_id'],
            $data['target_text'] ?? null
        );

        $this->translationUnits[$unit->getId()] = $unit;
        return $unit->toArray();
    }

    public function update(string $id, array $data): array
    {
        if (!isset($this->translationUnits[$id])) {
            throw new InvalidArgumentException('Translation unit not found');
        }

        $unit = $this->translationUnits[$id];

        if (isset($data['target_text'])) {
            $unit->updateTargetText(
                $data['target_text'],
                $data['changed_by'] ?? 'system',
                $data['change_reason'] ?? null
            );
        }

        return $unit->toArray();
    }

    public function delete(string $id): void
    {
        if (!isset($this->translationUnits[$id])) {
            throw new InvalidArgumentException('Translation unit not found');
        }

        $this->translationUnits[$id]->delete();
    }

    public function getHistory(string $id): array
    {
        if (!isset($this->translationUnits[$id])) {
            throw new InvalidArgumentException('Translation unit not found');
        }

        return array_map(
            fn($entry) => $entry->toArray(),
            $this->translationUnits[$id]->getHistory()
        );
    }
} 