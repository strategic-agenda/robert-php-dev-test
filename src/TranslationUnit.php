<?php

declare(strict_types=1);

namespace App\Services\Translation;

use DateTime;
use InvalidArgumentException;

/**
 * Class TranslationUnit
 * 
 * Manages translation units with history tracking capabilities.
 */
class TranslationUnit
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var string
     */
    private string $sourceText;

    /**
     * @var string
     */
    private string $sourceLanguage;

    /**
     * @var string
     */
    private string $targetLanguage;

    /**
     * @var string|null
     */
    private ?string $targetText = null;

    /**
     * @var array
     */
    private array $history = [];

    /**
     * @var DateTime
     */
    private DateTime $createdAt;

    /**
     * @var DateTime
     */
    private DateTime $updatedAt;

    /**
     * TranslationUnit constructor.
     *
     * @param string $sourceText
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $sourceText,
        string $sourceLanguage,
        string $targetLanguage
    ) {
        $this->validateLanguageCode($sourceLanguage);
        $this->validateLanguageCode($targetLanguage);
        
        $this->id = uniqid('tu_', true);
        $this->sourceText = $sourceText;
        $this->sourceLanguage = $sourceLanguage;
        $this->targetLanguage = $targetLanguage;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * Get the translation unit ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the source text.
     *
     * @return string
     */
    public function getSourceText(): string
    {
        return $this->sourceText;
    }

    /**
     * Get the source language.
     *
     * @return string
     */
    public function getSourceLanguage(): string
    {
        return $this->sourceLanguage;
    }

    /**
     * Get the target language.
     *
     * @return string
     */
    public function getTargetLanguage(): string
    {
        return $this->targetLanguage;
    }

    /**
     * Get the target text.
     *
     * @return string|null
     */
    public function getTargetText(): ?string
    {
        return $this->targetText;
    }

    /**
     * Update the target text and keep history.
     *
     * @param string $newTargetText
     * @return void
     */
    public function updateTargetText(string $newTargetText): void
    {
        if ($this->targetText !== null) {
            $this->history[] = [
                'previousText' => $this->targetText,
                'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s')
            ];
        }

        $this->targetText = $newTargetText;
        $this->updatedAt = new DateTime();
    }

    /**
     * Get the translation history.
     *
     * @return array
     */
    public function getHistory(): array
    {
        return $this->history;
    }

    /**
     * Get creation date.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Get last update date.
     *
     * @return DateTime
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Validate language code format.
     *
     * @param string $languageCode
     * @throws InvalidArgumentException
     */
    private function validateLanguageCode(string $languageCode): void
    {
        if (!preg_match('/^[a-z]{2}$/', $languageCode)) {
            throw new InvalidArgumentException(
                'Language code must be a 2-letter ISO 639-1 code'
            );
        }
    }
} 