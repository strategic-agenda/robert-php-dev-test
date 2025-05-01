<?php

class TranslationUnit
{
    private $id;
    private $sourceText;
    private $sourceLanguage;
    private $translations;
    private $history;

    /**
     * Constructor to initialize a new TranslationUnit.
     *
     * @param string $id Unique identifier for the translation unit
     * @param string $sourceText The source text to be translated
     * @param string $sourceLanguage ISO 639-1 language code (e.g., 'en')
     */
    public function __construct(string $id, string $sourceText, string $sourceLanguage)
    {
        $this->id = $id;
        $this->sourceText = $sourceText;
        $this->sourceLanguage = $sourceLanguage;
        $this->translations = [];
        $this->history = [];
    }

    /**
     * Add a new translation to the translation unit.
     *
     * @param string $targetLanguage ISO 639-1 language code (e.g., 'es')
     * @param string $targetText The translated text
     * @param string $translatorId ID of the translator
     * @return bool Returns true if the translation was added successfully
     */
    public function addTranslation(string $targetLanguage, string $targetText, string $translatorId): bool
    {
        if (isset($this->translations[$targetLanguage])) {
            return false; // Translation for this language already exists
        }

        $translation = [
            'text' => $targetText,
            'translator_id' => $translatorId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->translations[$targetLanguage] = $translation;
        $this->history[] = [
            'action' => 'add',
            'target_language' => $targetLanguage,
            'text' => $targetText,
            'translator_id' => $translatorId,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return true;
    }

    /**
     * Retrieve the translation unit by ID.
     *
     * @return array Returns the translation unit details
     */
    public function getDetails(): array
    {
        return [
            'id' => $this->id,
            'source_text' => $this->sourceText,
            'source_language' => $this->sourceLanguage,
            'translations' => $this->translations,
            'history' => $this->history
        ];
    }

    /**
     * Update a translation for a specific language and keep history.
     *
     * @param string $targetLanguage ISO 639-1 language code
     * @param string $newText The updated translated text
     * @param string $translatorId ID of the translator
     * @param string|null $reason Optional reason for the update
     * @return bool Returns true if the translation was updated successfully
     */
    public function updateTranslation(string $targetLanguage, string $newText, string $translatorId, ?string $reason = null): bool
    {
        if (!isset($this->translations[$targetLanguage])) {
            return false; // Translation for this language does not exist
        }

        $oldTranslation = $this->translations[$targetLanguage];

        $this->translations[$targetLanguage]['text'] = $newText;
        $this->translations[$targetLanguage]['translator_id'] = $translatorId;
        $this->translations[$targetLanguage]['updated_at'] = date('Y-m-d H:i:s');

        $this->history[] = [
            'action' => 'update',
            'target_language' => $targetLanguage,
            'previous_text' => $oldTranslation['text'],
            'new_text' => $newText,
            'translator_id' => $translatorId,
            'reason' => $reason,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return true;
    }

    /**
     * Get the history of changes for the translation unit.
     *
     * @return array Returns the history of changes
     */
    public function getHistory(): array
    {
        return $this->history;
    }
}

// Usage Example
$unit = new TranslationUnit('unit_001', 'Hello, world!', 'en');

// Add a new translation
$unit->addTranslation('es', '¡Hola, mundo!', 'translator_001');
echo "After adding translation:\n";
print_r($unit->getDetails());

// Update the translation
$unit->updateTranslation('es', '¡Hola, mundo nuevo!', 'translator_002', 'Improved translation accuracy');
echo "\nAfter updating translation:\n";
print_r($unit->getDetails());

echo "\nHistory of changes:\n";
print_r($unit->getHistory());
?>