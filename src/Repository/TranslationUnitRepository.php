<?php

namespace App\Repository;

use App\Model\TranslationUnit;
use Doctrine\DBAL\Connection;

class TranslationUnitRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function create(TranslationUnit $unit): int
    {
        $data = $unit->toArray();
        unset($data['id'], $data['history']);

        $this->connection->insert('translation_units', $data);
        return (int) $this->connection->lastInsertId();
    }

    public function findById(int $id): ?TranslationUnit
    {
        $data = $this->connection->fetchAssociative(
            'SELECT * FROM translation_units WHERE id = ?',
            [$id]
        );

        if (!$data) {
            return null;
        }

        $unit = new TranslationUnit(
            $data['source_text'],
            $data['target_text'],
            $data['source_language'],
            $data['target_language']
        );

        // Load history
        $history = $this->connection->fetchAllAssociative(
            'SELECT * FROM translation_history WHERE unit_id = ? ORDER BY created_at DESC',
            [$id]
        );

        foreach ($history as $entry) {
            $unit->addHistoryEntry($entry['target_text'], $entry['created_at']);
        }

        return $unit;
    }

    public function update(TranslationUnit $unit): void
    {
        $data = $unit->toArray();
        $id = $data['id'];
        unset($data['id'], $data['history']);

        $this->connection->update(
            'translation_units',
            $data,
            ['id' => $id]
        );

        // Save history entry
        $lastHistoryEntry = end($unit->getHistory());
        if ($lastHistoryEntry) {
            $this->connection->insert('translation_history', [
                'unit_id' => $id,
                'target_text' => $lastHistoryEntry['targetText'],
                'created_at' => $lastHistoryEntry['updatedAt']
            ]);
        }
    }

    public function findAll(int $limit = 10, int $offset = 0): array
    {
        $data = $this->connection->fetchAllAssociative(
            'SELECT * FROM translation_units ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$limit, $offset]
        );

        $units = [];
        foreach ($data as $row) {
            $unit = new TranslationUnit(
                $row['source_text'],
                $row['target_text'],
                $row['source_language'],
                $row['target_language']
            );
            $units[] = $unit;
        }

        return $units;
    }
} 