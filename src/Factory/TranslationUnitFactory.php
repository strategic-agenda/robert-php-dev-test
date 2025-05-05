<?php

namespace Robert\CAT\Factory;

use Robert\CAT\TranslationUnit;

/**
 * Class TranslationUnitFactory
 * 
 * Factory for creating TranslationUnit objects.
 * Implements the Factory pattern.
 */
class TranslationUnitFactory
{
    /**
     * Create a translation unit from text
     * 
     * @param int $documentId
     * @param int $sequenceNumber
     * @param string $sourceText
     * @param string|null $context
     * @return TranslationUnit
     */
    public function createFromText(
        int $documentId,
        int $sequenceNumber,
        string $sourceText,
        ?string $context = null
    ): TranslationUnit {
        $unit = new TranslationUnit($documentId, $sequenceNumber, $sourceText);

        if ($context !== null) {
            $unit->setContext($context);
        }

        return $unit;
    }

    /**
     * Create translation units from a document text
     * 
     * @param int $documentId
     * @param string $documentText
     * @param string $segmentationType Type of segmentation (sentence, paragraph)
     * @return array Array of TranslationUnit objects
     */
    public function createFromDocument(
        int $documentId,
        string $documentText,
        string $segmentationType = 'sentence'
    ): array {
        $segments = $this->segmentText($documentText, $segmentationType);
        $units = [];

        $sequenceNumber = 1;
        foreach ($segments as $segment) {
            if (trim($segment) === '') {
                continue;
            }

            $units[] = $this->createFromText($documentId, $sequenceNumber++, $segment);
        }

        return $units;
    }

    /**
     * Segment text based on specified segmentation type
     * 
     * @param string $text
     * @param string $segmentationType
     * @return array
     */
    private function segmentText(string $text, string $segmentationType): array
    {
        switch ($segmentationType) {
            case 'paragraph':
                return $this->segmentByParagraph($text);

            case 'sentence':
            default:
                return $this->segmentBySentence($text);
        }
    }

    /**
     * Segment text by paragraphs
     * 
     * @param string $text
     * @return array
     */
    private function segmentByParagraph(string $text): array
    {
        // Split by double line breaks for paragraphs
        $segments = preg_split('/\n\s*\n/', $text);
        return array_map('trim', $segments);
    }

    /**
     * Segment text by sentences
     * 
     * @param string $text
     * @return array
     */
    private function segmentBySentence(string $text): array
    {
        // Basic sentence segmentation - can be improved with NLP libraries
        $pattern = '/(?<=[.!?])\s+(?=[A-Z])/';
        $segments = preg_split($pattern, $text);
        return array_map('trim', $segments);
    }

    /**
     * Create a translation unit from database data
     * 
     * @param array $data
     * @return TranslationUnit
     */
    public function createFromArray(array $data): TranslationUnit
    {
        $unit = new TranslationUnit(
            (int)$data['document_id'],
            (int)$data['sequence_number'],
            $data['source_content']
        );

        if (isset($data['id'])) {
            $unit->setId((int)$data['id']);
        }

        if (isset($data['context'])) {
            $unit->setContext($data['context']);
        }

        // Handle translations if present
        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $languageId => $translation) {
                $unit->addTranslation(
                    (int)$languageId,
                    $translation['content'],
                    (int)($translation['translated_by'] ?? 1)
                );

                if (isset($translation['status']) && $translation['status'] !== 'draft') {
                    $unit->updateTranslationStatus(
                        (int)$languageId,
                        $translation['status'],
                        (int)($translation['reviewed_by'] ?? 1)
                    );
                }
            }
        }

        return $unit;
    }

    /**
     * Create a batch of translation units
     * 
     * @param array $data Array of unit data
     * @return array Array of TranslationUnit objects
     */
    public function createBatch(array $data): array
    {
        $units = [];

        foreach ($data as $unitData) {
            $units[] = $this->createFromArray($unitData);
        }

        return $units;
    }
}
