<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;
use InvalidArgumentException;

class TranslationUnit
{
    private string $id;
    private string $sourceText;
    private string $sourceLanguage;
    private string $targetLanguage;
    private ?string $targetText;
    private string $projectId;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $deletedAt;
    private array $history;

    public function __construct(
        string $sourceText,
        string $sourceLanguage,
        string $targetLanguage,
        string $projectId,
        ?string $targetText = null
    ) {
        $this->validateLanguageCode($sourceLanguage);
        $this->validateLanguageCode($targetLanguage);

        $this->id = uniqid('tu_', true);
        $this->sourceText = $sourceText;
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->targetText = $targetText;
        $this->projectId = $projectId;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->deletedAt = null;
        $this->history = [];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSourceText(): string
    {
        return $this->sourceText;
    }

    public function getSourceLanguage(): string
    {
        return $this->sourceLanguage;
    }

    public function getTargetLanguage(): string
    {
        return $this->targetLanguage;
    }

    public function getTargetText(): ?string
    {
        return $this->targetText;
    }

    public function getProjectId(): string
    {
        return $this->projectId;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function updateTargetText(string $newText, string $changedBy, ?string $changeReason = null): void
    {
        if ($this->deletedAt !== null) {
            throw new InvalidArgumentException('Cannot update a deleted translation unit');
        }

        $previousText = $this->targetText;
        $this->targetText = $newText;
        $this->updatedAt = new DateTime();

        // Create history entry
        $historyEntry = new TranslationHistory(
            $this->id,
            $previousText ?? '',
            $newText,
            $changedBy,
            $changeReason
        );

        $this->history[] = $historyEntry;
    }

    public function delete(): void
    {
        if ($this->deletedAt !== null) {
            throw new InvalidArgumentException('Translation unit is already deleted');
        }

        $this->deletedAt = new DateTime();
    }

    public function restore(): void
    {
        if ($this->deletedAt === null) {
            throw new InvalidArgumentException('Translation unit is not deleted');
        }

        $this->deletedAt = null;
        $this->updatedAt = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'source_text' => $this->sourceText,
            'source_language' => $this->sourceLanguage,
            'target_language' => $this->targetLanguage,
            'target_text' => $this->targetText,
            'project_id' => $this->projectId,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
            'history' => array_map(fn($entry) => $entry->toArray(), $this->history)
        ];
    }

    private function validateLanguageCode(string $code): void
    {
        if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $code)) {
            throw new InvalidArgumentException(
                'Invalid language code format. Expected format: xx or xx-XX (e.g., en, en-US)'
            );
        }
    }
} 