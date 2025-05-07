<?php

declare(strict_types=1);

/**
 * TranslationUnit class for managing translation units
 * 
 * Provides functionality to add, retrieve, and update translation units
 * while keeping a history of changes.
 */
class TranslationUnit
{
    /**
     * PDO database connection
     */
    private PDO $pdo;
    
    /**
     * Constructor
     * 
     * @param PDO $pdo PDO database connection
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Add a new translation unit
     * 
     * @param string $sourceText source text of the translation unit
     * @param array $translations initial translations for the unit
     * @return int|false returns the new unit ID if successful, false otherwise
     */
    public function add(string $sourceText, array $translations = []): int|false
    {
        try {
            // begin transaction
            $this->pdo->beginTransaction();
            
            // insert the translation unit
            $stmt = $this->pdo->prepare('
                INSERT INTO translation_units (source_text)
                VALUES (:source_text)
            ');
            
            $stmt->execute([
                'source_text' => $sourceText
            ]);
            
            // get the ID of the newly created unit
            $unitId = (int) $this->pdo->lastInsertId();
            
            // Add translations if provided
            if (!empty($translations)) {
                foreach ($translations as $languageCode => $translationText) {
                    $this->addTranslation($unitId, $languageCode, $translationText);
                }
            }
            
            $this->pdo->commit();

            return $unitId;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log('Failed to add translation unit: ' . $e->getMessage());

            return false;
        }
    }
    
    /**
     * Add a translation for a translation unit
     * 
     * @param int $unitId ID of the translation unit
     * @param string $languageCode language code (e.g., 'en', 'fr', 'es')
     * @param string $translationText translated text
     * @return bool returns true if successful, false otherwise
     */
    private function addTranslation(int $unitId, string $languageCode, string $translationText): bool
    {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO translations (unit_id, language_code, translation_text)
                VALUES (:unit_id, :language_code, :translation_text)
            ');
            
            return $stmt->execute([
                'unit_id' => $unitId,
                'language_code' => $languageCode,
                'translation_text' => $translationText
            ]);
        } catch (PDOException $e) {
            error_log('Failed to add translation: ' . $e->getMessage());

            return false;
        }
    }
    
    /**
     * Retrieve a translation unit by its ID
     * 
     * @param int $id ID of the translation unit to retrieve
     * @return array|null translation unit with its translations or null if not found
     */
    public function get(int $id): ?array
    {
        try {
            // get the translation unit
            $stmt = $this->pdo->prepare('
                SELECT id, source_text, created_at, updated_at
                FROM translation_units
                WHERE id = :id
            ');
            
            $stmt->execute(['id' => $id]);
            $unit = $stmt->fetch();
            
            if (!$unit) {
                return null;
            }
            
            // Add translations to the unit
            $unit['translations'] = $this->getTranslationsForUnit($id);
            
            return $unit;
        } catch (PDOException $e) {
            error_log('Failed to retrieve translation unit: ' . $e->getMessage());

            return null;
        }
    }
    
    /**
     * Update a translation unit and keep the history of changes
     * 
     * @param int $id ID of the translation unit to update
     * @param string|null $sourceText new source text (or null to keep current)
     * @param array|null $translations new translations (or null to keep current)
     * @return bool returns true if the update was successful and false otherwise
     */
    public function update(int $id, ?string $sourceText = null, ?array $translations = null): bool
    {
        try {
            // begin transaction
            $this->pdo->beginTransaction();
            
            // check if the unit exists
            $checkStmt = $this->pdo->prepare('
                SELECT id FROM translation_units WHERE id = :id
            ');
            
            $checkStmt->execute(['id' => $id]);

            if (!$checkStmt->fetch()) {
                return false;
            }
            
            // update source text if provided
            if ($sourceText !== null) {
                $updateStmt = $this->pdo->prepare('
                    UPDATE translation_units
                    SET source_text = :source_text
                    WHERE id = :id
                ');
                
                $updateStmt->execute([
                    'id' => $id,
                    'source_text' => $sourceText
                ]);
            }
            
            // update translations if provided
            if ($translations !== null) {
                // get existing translations to identify removals
                $currentTranslations = $this->getTranslationsForUnit($id);
                
                // check for translations to remove (exist in current but not in new)
                foreach ($currentTranslations as $langCode => $text) {
                    if (!array_key_exists($langCode, $translations)) {
                        $this->removeTranslation($id, $langCode);
                    }
                }
                
                // Add or update translations
                foreach ($translations as $languageCode => $translationText) {
                    $this->updateTranslation($id, $languageCode, $translationText);
                }
            }
            
            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log('Failed to update translation unit: ' . $e->getMessage());

            return false;
        }
    }
    
    /**
     * Update or add a translation for a translation unit while keeping history
     * 
     * @param int $unitId The ID of the translation unit
     * @param string $languageCode language code
     * @param string $translationText new translated text
     * @return bool returns true if successful, false otherwise
     */
    private function updateTranslation(int $unitId, string $languageCode, string $translationText): bool
    {
        try {
            // check if the translation exists
            $checkStmt = $this->pdo->prepare('
                SELECT id, translation_text
                FROM translations
                WHERE unit_id = :unit_id AND language_code = :language_code
            ');
            
            $checkStmt->execute([
                'unit_id' => $unitId,
                'language_code' => $languageCode
            ]);
            
            $translation = $checkStmt->fetch();
            
            if ($translation) {
                // translation exists, update it and create a new version
                $translationId = $translation['id'];
                $oldTranslationText = $translation['translation_text'];
                
                // only update if the text has changed
                if ($oldTranslationText !== $translationText) {
                    // get the latest version number
                    $versionStmt = $this->pdo->prepare('
                        SELECT MAX(version_number) as last_version
                        FROM translation_versions
                        WHERE translation_id = :translation_id
                    ');
                    
                    $versionStmt->execute(['translation_id' => $translationId]);
                    $versionData = $versionStmt->fetch();
                    $newVersion = ($versionData['last_version'] ?? 0) + 1;
                    
                    // create a new version
                    $newVersionStmt = $this->pdo->prepare('
                        INSERT INTO translation_versions 
                            (translation_id, translation_text, version_number)
                        VALUES 
                            (:translation_id, :translation_text, :version_number)
                    ');
                    
                    $newVersionStmt->execute([
                        'translation_id' => $translationId,
                        'translation_text' => $oldTranslationText,
                        'version_number' => $newVersion
                    ]);
                    
                    // Update the current translation
                    $updateStmt = $this->pdo->prepare('
                        UPDATE translations
                        SET translation_text = :translation_text
                        WHERE id = :id
                    ');
                    
                    $updateStmt->execute([
                        'id' => $translationId,
                        'translation_text' => $translationText
                    ]);
                }
                
                return true;
            }

            // translation doesn't exist, add it
            return $this->addTranslation($unitId, $languageCode, $translationText);
        } catch (PDOException $e) {
            error_log('Failed to update translation: ' . $e->getMessage());

            return false;
        }
    }
    
    /**
     * Get the history of translations for a translation unit
     * 
     * @param int $id ID of the translation unit
     * @param string|null $languageCode optional language code filter
     * @return array list of translation versions grouped by language
     */
    public function getHistory(int $id, ?string $languageCode = null): array
    {
        try {
            $params = ['unit_id' => $id];
            $languageFilter = '';
            
            if ($languageCode !== null) {
                $languageFilter = 'AND t.language_code = :language_code';
                $params['language_code'] = $languageCode;
            }
            
            $stmt = $this->pdo->prepare("
                SELECT 
                    t.language_code,
                    tv.translation_text,
                    tv.version_number,
                    tv.created_at
                FROM translations t
                INNER JOIN translation_versions tv ON t.id = tv.translation_id
                WHERE t.unit_id = :unit_id {$languageFilter}
                ORDER BY t.language_code, tv.version_number DESC
            ");
            
            $stmt->execute($params);
            $versions = $stmt->fetchAll();
            
            // group versions by language
            $history = [];

            foreach ($versions as $version) {
                $langCode = $version['language_code'];

                if (!isset($history[$langCode])) {
                    $history[$langCode] = [];
                }

                $history[$langCode][] = [
                    'text' => $version['translation_text'],
                    'version' => $version['version_number'],
                    'created_at' => $version['created_at']
                ];
            }
            
            return $history;
        } catch (PDOException $e) {
            error_log('Failed to retrieve translation history: ' . $e->getMessage());

            return [];
        }
    }
    
    /**
     * Remove a translation for a translation unit
     * 
     * @param int $unitId ID of the translation unit
     * @param string $languageCode language code to remove
     * @return bool returns true if successful, false otherwise
     */
    private function removeTranslation(int $unitId, string $languageCode): bool
    {
        try {
            // find the translation ID
            $getStmt = $this->pdo->prepare('
                SELECT id
                FROM translations
                WHERE unit_id = :unit_id AND language_code = :language_code
            ');
            
            $getStmt->execute([
                'unit_id' => $unitId,
                'language_code' => $languageCode
            ]);
            
            $translation = $getStmt->fetch();
            
            if (!$translation) {
                // translation doesn't exist, nothing to remove
                return true;
            }
            
            $translationId = $translation['id'];

            // remove the translation - related version history will be deleted automatically
            $deleteStmt = $this->pdo->prepare('
                DELETE FROM translations
                WHERE id = :id
            ');
            
            return $deleteStmt->execute(['id' => $translationId]);
        } catch (PDOException $e) {
            error_log('Failed to remove translation: ' . $e->getMessage());
            
            return false;
        }
    }
    
    /**
     * Get translations for a unit and group them by language code
     * 
     * @param int $unitId ID of the translation unit
     * @return array translations grouped by language code
     */
    private function getTranslationsForUnit(int $unitId): array
    {
        $translationsStmt = $this->pdo->prepare('
            SELECT language_code, translation_text
            FROM translations
            WHERE unit_id = :unit_id
        ');
        
        $translationsStmt->execute(['unit_id' => $unitId]);
        $translationsRows = $translationsStmt->fetchAll();
    
        // group translations by language code
        $translations = [];
        foreach ($translationsRows as $translation) {
            $translations[$translation['language_code']] = $translation['translation_text'];
        }
        
        return $translations;
    }
    
    /**
     * Get the list of all translation units
     *
     * @return array list of all translation units with their translations
     */
    public function getAll(): array
    {
        try {
            $stmt = $this->pdo->query('
                SELECT id, source_text, created_at, updated_at
                FROM translation_units
            ');

            $units = $stmt->fetchAll();
            
            // get translations for each unit
            foreach ($units as &$unit) {
                $unit['translations'] = $this->getTranslationsForUnit($unit['id']);
            }
            unset($unit);
            
            return $units;
        } catch (PDOException $e) {
            error_log('Failed to retrieve all translation units: ' . $e->getMessage());

            return [];
        }
    }
}
