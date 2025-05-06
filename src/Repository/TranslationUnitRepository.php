<?php

namespace Robert\CAT\Repository;

use PDO;
use Robert\CAT\TranslationUnit;

/**
 * TranslationUnitRepository
 * 
 * Handles database operations for TranslationUnit entities.
 * Implements the Repository pattern.
 */
class TranslationUnitRepository implements TranslationUnitRepositoryInterface
{
    /** @var PDO Database connection */
    private PDO $pdo;

    /**
     * Constructor
     * 
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Find a translation unit by ID
     * 
     * @param int $id
     * @return TranslationUnit|null
     */
    public function findById(int $id): ?TranslationUnit
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM translation_units WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }

        return $this->hydrateUnit($data);
    }

    /**
     * Find translation units by document ID
     * 
     * @param int $documentId
     * @return array
     */
    public function findByDocumentId(int $documentId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM translation_units WHERE document_id = :document_id ORDER BY sequence_number"
        );
        $stmt->execute(['document_id' => $documentId]);

        $units = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $units[] = $this->hydrateUnit($data);
        }

        return $units;
    }

    /**
     * Save a translation unit
     * 
     * @param TranslationUnit $unit
     * @return void
     */
    public function save(TranslationUnit $unit): void
    {
        if ($unit->getId() === null) {
            $this->insert($unit);
        } else {
            $this->update($unit);
        }

        // Save translations
        $this->saveTranslations($unit);
    }

    /**
     * Insert a new translation unit
     * 
     * @param TranslationUnit $unit
     * @return void
     */
    private function insert(TranslationUnit $unit): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO translation_units 
             (document_id, sequence_number, source_content, context) 
             VALUES (:document_id, :sequence_number, :source_content, :context)"
        );

        $stmt->execute([
            'document_id' => $unit->getDocumentId(),
            'sequence_number' => $unit->getSequenceNumber(),
            'source_content' => $unit->getSourceContent(),
            'context' => $unit->getContext()
        ]);

        $id = $this->pdo->lastInsertId();
        $unit->setId((int)$id);
    }

    /**
     * Update an existing translation unit
     * 
     * @param TranslationUnit $unit
     * @return void
     */
    private function update(TranslationUnit $unit): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE translation_units 
             SET source_content = :source_content, 
                 context = :context, 
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );

        $stmt->execute([
            'id' => $unit->getId(),
            'source_content' => $unit->getSourceContent(),
            'context' => $unit->getContext()
        ]);
    }

    /**
     * Save translations for a unit
     * 
     * @param TranslationUnit $unit
     * @return void
     */
    private function saveTranslations(TranslationUnit $unit): void
    {
        $translations = $unit->getTranslations();

        foreach ($translations as $languageId => $translation) {
            // Check if translation exists
            $stmt = $this->pdo->prepare(
                "SELECT id FROM translations 
                 WHERE translation_unit_id = :unit_id AND language_id = :language_id"
            );
            $stmt->execute([
                'unit_id' => $unit->getId(),
                'language_id' => $languageId
            ]);

            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                // Update existing translation
                $stmt = $this->pdo->prepare(
                    "UPDATE translations 
                     SET content = :content, 
                         status = :status, 
                         translated_by = :translated_by,
                         reviewed_by = :reviewed_by,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id"
                );

                $stmt->execute([
                    'id' => $existingId,
                    'content' => $translation['content'],
                    'status' => $translation['status'],
                    'translated_by' => $translation['translated_by'],
                    'reviewed_by' => $translation['reviewed_by'] ?? null
                ]);
            } else {
                // Insert new translation
                $stmt = $this->pdo->prepare(
                    "INSERT INTO translations 
                     (translation_unit_id, language_id, content, status, translated_by) 
                     VALUES (:unit_id, :language_id, :content, :status, :translated_by)"
                );

                $stmt->execute([
                    'unit_id' => $unit->getId(),
                    'language_id' => $languageId,
                    'content' => $translation['content'],
                    'status' => $translation['status'],
                    'translated_by' => $translation['translated_by']
                ]);
            }
        }
    }

    /**
     * Hydrate a TranslationUnit from database data
     * 
     * @param array $data
     * @return TranslationUnit
     */
    private function hydrateUnit(array $data): TranslationUnit
    {
        $unit = new TranslationUnit(
            (int)$data['document_id'],
            (int)$data['sequence_number'],
            $data['source_content']
        );

        $unit->setId((int)$data['id']);
        $unit->setContext($data['context']);

        // Load translations
        $this->loadTranslations($unit);

        return $unit;
    }

    /**
     * Load translations for a unit
     * 
     * @param TranslationUnit $unit
     * @return void
     */
    private function loadTranslations(TranslationUnit $unit): void
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, u1.id as translator_id, u2.id as reviewer_id
             FROM translations t
             LEFT JOIN users u1 ON t.translated_by = u1.id
             LEFT JOIN users u2 ON t.reviewed_by = u2.id
             WHERE t.translation_unit_id = :unit_id"
        );
        $stmt->execute(['unit_id' => $unit->getId()]);

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $unit->addTranslation(
                (int)$data['language_id'],
                $data['content'],
                (int)$data['translator_id']
            );

            // Update status if needed
            if ($data['status'] !== 'draft') {
                $unit->updateTranslationStatus(
                    (int)$data['language_id'],
                    $data['status'],
                    (int)$data['reviewer_id']
                );
            }
        }
    }

    /**
     * Delete a translation unit
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM translation_units WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
