<?php

namespace App\Model;

class TranslationUnit
{
    private ?int $id = null;
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
        
        // Initialize history with the initial target text
        $this->history[] = [
            'targetText' => $targetText,
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
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

    public function addHistoryEntry(string $targetText, string $createdAt): void
    {
        array_unshift($this->history, [
            'targetText' => $targetText,
            'updatedAt' => $createdAt
        ]);
    }

    public function setTargetText(string $newTargetText): void
    {
        $this->updatedAt = new \DateTime();
        
        // Add the new translation to history
        array_unshift($this->history, [
            'targetText' => $newTargetText,
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ]);

        $this->targetText = $newTargetText;
    }

    public function toArray(): array
    {
        $data = [
            'sourceText' => $this->sourceText,
            'targetText' => $this->targetText,
            'sourceLanguage' => $this->sourceLanguage,
            'targetLanguage' => $this->targetLanguage,
            'history' => $this->history,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
        ];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        return $data;
    }
} 