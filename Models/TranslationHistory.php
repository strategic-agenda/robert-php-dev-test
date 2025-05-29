<?php

declare(strict_types=1);

namespace App\Models;

use DateTime;

class TranslationHistory
{
    private string $id;
    private string $translationUnitId;
    private string $previousText;
    private string $newText;
    private string $changedBy;
    private ?string $changeReason;
    private DateTime $createdAt;

    public function __construct(
        string $translationUnitId,
        string $previousText,
        string $newText,
        string $changedBy,
        ?string $changeReason = null
    ) {
        $this->id = uniqid('th_', true);
        $this->translationUnitId = $translationUnitId;
        $this->previousText = $previousText;
        $this->newText = $newText;
        $this->changedBy = $changedBy;
        $this->changeReason = $changeReason;
        $this->createdAt = new DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTranslationUnitId(): string
    {
        return $this->translationUnitId;
    }

    public function getPreviousText(): string
    {
        return $this->previousText;
    }

    public function getNewText(): string
    {
        return $this->newText;
    }

    public function getChangedBy(): string
    {
        return $this->changedBy;
    }

    public function getChangeReason(): ?string
    {
        return $this->changeReason;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'translation_unit_id' => $this->translationUnitId,
            'previous_text' => $this->previousText,
            'new_text' => $this->newText,
            'changed_by' => $this->changedBy,
            'change_reason' => $this->changeReason,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s')
        ];
    }
} 