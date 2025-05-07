<?php

namespace App\Repository;

use App\Model\TranslationUnit;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

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

        // Map camelCase properties to snake_case database columns
        $dbData = [
            'source_text' => $data['sourceText'],
            'target_text' => $data['targetText'],
            'source_language' => $data['sourceLanguage'],
            'target_language' => $data['targetLanguage'],
            'created_at' => $data['createdAt'],
            'updated_at' => $data['updatedAt']
        ];

        $this->connection->insert('translation_units', $dbData);
        $id = (int) $this->connection->lastInsertId();
        $unit->setId($id);
        return $id;
    }

    /**
     * @throws Exception
     */
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
        $unit->setId($data['id']);

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

        // Map camelCase properties to snake_case database columns
        $dbData = [
            'source_text' => $data['sourceText'],
            'target_text' => $data['targetText'],
            'source_language' => $data['sourceLanguage'],
            'target_language' => $data['targetLanguage'],
            'updated_at' => $data['updatedAt']
        ];

        $this->connection->update(
            'translation_units',
            $dbData,
            ['id' => $id]
        );

        // Save history entry
        $array = $unit->getHistory();
        $lastHistoryEntry = end($array);
        if ($lastHistoryEntry) {
            $this->connection->insert('translation_history', [
                'unit_id' => $id,
                'target_text' => $lastHistoryEntry['targetText'],
                'created_at' => $lastHistoryEntry['updatedAt']
            ]);
        }
    }

    /**
     * @throws Exception
     */
    public function findAll(int $limit = 10, int $offset = 0): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
           ->from('translation_units')
           ->orderBy('created_at', 'DESC')
           ->setMaxResults($limit)
           ->setFirstResult($offset);

        $data = $qb->executeQuery()->fetchAllAssociative();

        $units = [];
        foreach ($data as $row) {
            $unit = new TranslationUnit(
                $row['source_text'],
                $row['target_text'],
                $row['source_language'],
                $row['target_language']
            );
            $unit->setId($row['id']);
            $units[] = $unit;
        }

        return $units;
    }
}
