<?php

namespace Robert\CAT\Repository;

use Robert\CAT\TranslationUnit;

/**
 * Interface TranslationUnitRepositoryInterface
 * 
 * Defines methods for translation unit repository implementations.
 */
interface TranslationUnitRepositoryInterface
{
    /**
     * Find a translation unit by ID
     * 
     * @param int $id
     * @return TranslationUnit|null
     */
    public function findById(int $id): ?TranslationUnit;

    /**
     * Find translation units by document ID
     * 
     * @param int $documentId
     * @return array
     */
    public function findByDocumentId(int $documentId): array;

    /**
     * Save a translation unit
     * 
     * @param TranslationUnit $unit
     * @return void
     */
    public function save(TranslationUnit $unit): void;

    /**
     * Delete a translation unit
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
