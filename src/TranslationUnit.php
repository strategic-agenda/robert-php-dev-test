<?php

namespace Robert\CAT;

use JsonSerializable;

/**
 * Class TranslationUnit
 * 
 * Represents a unit of translation in the Robert CAT tool.
 */
class TranslationUnit implements JsonSerializable
{
    /** @var int|null The unit ID */
    private ?int $id = null;

    /** @var int The document ID this unit belongs to */
    private int $documentId;

    /** @var int The sequence number within the document */
    private int $sequenceNumber;

    /** @var string The source content to be translated */
    private string $sourceContent;

    /** @var string|null Additional context for the translation */
    private ?string $context = null;

    /** @var array Array of translations for this unit */
    private array $translations = [];

    /**
     * Constructor
     * 
     * @param int $documentId The document ID
     * @param int $sequenceNumber The sequence number in the document
     * @param string $sourceContent The source content to translate
     */
    public function __construct(int $documentId, int $sequenceNumber, string $sourceContent)
    {
        $this->documentId = $documentId;
        $this->sequenceNumber = $sequenceNumber;
        $this->sourceContent = $sourceContent;
    }

    /**
     * Get the unit ID
     * 
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the unit ID
     * 
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the document ID
     * 
     * @return int
     */
    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    /**
     * Get the sequence number
     * 
     * @return int
     */
    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    /**
     * Get the source content
     * 
     * @return string
     */
    public function getSourceContent(): string
    {
        return $this->sourceContent;
    }

    /**
     * Set the source content
     * 
     * @param string $content
     * @return self
     */
    public function setSourceContent(string $content): self
    {
        $this->sourceContent = $content;
        return $this;
    }

    /**
     * Get the context
     * 
     * @return string|null
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * Set the context
     * 
     * @param string|null $context
     * @return self
     */
    public function setContext(?string $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Add a translation for a specific language
     * 
     * @param int $languageId The language ID
     * @param string $content The translated content
     * @param int $translatedBy User ID of the translator
     * @return self
     */
    public function addTranslation(int $languageId, string $content, int $translatedBy): self
    {
        $this->translations[$languageId] = [
            'content' => $content,
            'translated_by' => $translatedBy,
            'status' => 'draft',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this;
    }

    /**
     * Get a translation for a specific language
     * 
     * @param int $languageId
     * @return array|null The translation data or null if not found
     */
    public function getTranslation(int $languageId): ?array
    {
        return $this->translations[$languageId] ?? null;
    }

    /**
     * Get all translations
     * 
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * Update the status of a translation
     * 
     * @param int $languageId The language ID
     * @param string $status The new status (draft, reviewed, approved, rejected)
     * @param int $reviewedBy User ID who reviewed the translation
     * @return self
     */
    public function updateTranslationStatus(int $languageId, string $status, int $reviewedBy): self
    {
        if (isset($this->translations[$languageId])) {
            $this->translations[$languageId]['status'] = $status;
            $this->translations[$languageId]['reviewed_by'] = $reviewedBy;
            $this->translations[$languageId]['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this;
    }

    /**
     * Convert to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'document_id' => $this->documentId,
            'sequence_number' => $this->sequenceNumber,
            'source_content' => $this->sourceContent,
            'context' => $this->context,
            'translations' => $this->translations,
        ];
    }

    /**
     * JSON serialization
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
