<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use DateTime;

/**
 * TranslationHistory
 * 
 * Model class representing the history of changes made to translation units.
 * Tracks previous and new text values, who made the change, and when.
 */
class TranslationHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'translation_unit_id',
        'previous_text',
        'new_text',
        'changed_by',
        'change_reason'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime'
    ];

    /**
     * The translation unit ID.
     *
     * @var string
     */
    private string $id;

    /**
     * The ID of the associated translation unit.
     *
     * @var string
     */
    private string $translationUnitId;

    /**
     * The previous text value.
     *
     * @var string
     */
    private string $previousText;

    /**
     * The new text value.
     *
     * @var string
     */
    private string $newText;

    /**
     * The user who made the change.
     *
     * @var string
     */
    private string $changedBy;

    /**
     * The reason for the change.
     *
     * @var string|null
     */
    private ?string $changeReason;

    /**
     * The creation timestamp.
     *
     * @var DateTime
     */
    private DateTime $createdAt;

    /**
     * Create a new translation history instance.
     *
     * @param string $translationUnitId The translation unit ID
     * @param string $previousText The previous text value
     * @param string $newText The new text value
     * @param string $changedBy The user who made the change
     * @param string|null $changeReason The reason for the change
     */
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

    /**
     * Get the history entry ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the translation unit ID.
     *
     * @return string
     */
    public function getTranslationUnitId(): string
    {
        return $this->translationUnitId;
    }

    /**
     * Get the previous text value.
     *
     * @return string
     */
    public function getPreviousText(): string
    {
        return $this->previousText;
    }

    /**
     * Get the new text value.
     *
     * @return string
     */
    public function getNewText(): string
    {
        return $this->newText;
    }

    /**
     * Get the user who made the change.
     *
     * @return string
     */
    public function getChangedBy(): string
    {
        return $this->changedBy;
    }

    /**
     * Get the reason for the change.
     *
     * @return string|null
     */
    public function getChangeReason(): ?string
    {
        return $this->changeReason;
    }

    /**
     * Get the creation timestamp.
     *
     * @return DateTime
     */
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

    public function translationUnit(): BelongsTo
    {
        return $this->belongsTo(TranslationUnit::class);
    }
} 