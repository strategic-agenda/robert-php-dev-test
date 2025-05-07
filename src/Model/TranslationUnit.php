<?php

namespace App\Model;

class TranslationUnit
{
    private int $id;
    private string $sourceText;
    private string $targetText;
    private string $sourceLanguage;
    private string $targetLanguage;
    private array $history = [];
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        string $sourceText,
        string $targetText,
        string $sourceLanguage,
        string $targetLanguage
    ) {
        $this->sourceText = $sourceText;
        $this->targetText = $targetText;
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSourceText(): string
    {
        return $this->sourceText;
    }

    public function getTargetText(): string
    {
        return $this->targetText;
    }

    public function getSourceLanguage(): string
    {
        return $this->sourceLanguage;
    }

    public function getTargetLanguage(): string
    {
        return $this->targetLanguage;
    }

    public function getHistory(): array
    {
        return $this->history;
    }

    public function updateTranslation(string $newTargetText): void
    {
        // Add current translation to history
        $this->history[] = [
            'targetText' => $this->targetText,
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];

        $this->targetText = $newTargetText;
        $this->updatedAt = new \DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sourceText' => $this->sourceText,
            'targetText' => $this->targetText,
            'sourceLanguage' => $this->sourceLanguage,
            'targetLanguage' => $this->targetLanguage,
            'history' => $this->history,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
} 