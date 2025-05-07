<?php

class Translation
{
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all the translations based on translation unit id
     */
    public function getAllByUnitId($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, l.name as translated_language_name 
                FROM translations as t
                INNER JOIN languages as l on t.translated_language_id = l.id
                WHERE translation_unit_id = ?
                ORDER BY t.id DESC
            ");
            $stmt->execute([$id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching translations by unit ID: " . $e->getMessage());
            return ['error' => 'Unable to fetch translations'];
        }
    }

    /**
     * Get the translation by given id
     */
    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, l.name as translated_language_name 
                FROM translations as t
                INNER JOIN languages as l on t.translated_language_id = l.id
                WHERE t.id = ?
            ");
            $stmt->execute([$id]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching translation by ID: " . $e->getMessage());
            return ['error' => 'Translation not found'];
        }
    }

    /**
     * Create the translation based on given translated text and translated language
     */
    public function create($translationUnitId, $translatedText, $translatedLanguageId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO translations (translation_unit_id, translated_text, translated_language_id)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$translationUnitId, $translatedText, $translatedLanguageId]);

            return ['message' => 'Translation has been created.', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error creating translation: " . $e->getMessage());
            return ['error' => 'Unable to create translation'];
        }
    }

    /**
     * Update the translation based on given translated text and translated language
     */
    public function update($id, $translatedText, $translatedLanguageId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM translations WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$existing) {
                return ['error' => 'Translation not found'];
            }
    
            $stmt = $this->db->prepare("
                SELECT MAX(version) as max_version FROM translation_history WHERE translation_id = ?
            ");
            $stmt->execute([$id]);
            $maxVersion = $stmt->fetchColumn();
            $newVersion = $maxVersion ? $maxVersion + 1 : 1;
    
            $stmt = $this->db->prepare("
                INSERT INTO translation_history 
                (translation_id, translation_unit_id, translated_text, translated_language_id, version) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                $existing['translation_unit_id'],
                $existing['translated_text'],
                $existing['translated_language_id'],
                $newVersion
            ]);
    
            $stmt = $this->db->prepare("
                UPDATE translations SET translated_text = ?, translated_language_id = ?, updated_at = NOW() WHERE id = ?
            ");
            $stmt->execute([$translatedText, $translatedLanguageId, $id]);
    
            return ['message' => 'Translation updated and history stored.'];
        } catch (PDOException $e) {
            error_log("Error updating translation with history: " . $e->getMessage());
            return ['error' => 'Unable to update translation'];
        }
    }    

    /**
     * Delete the translation based on given id
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM translation_history WHERE translation_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM translations WHERE id = ?");
            $stmt->execute([$id]);

            return ['message' => 'Translation has been deleted.'];
        } catch (PDOException $e) {
            error_log("Error deleting translation: " . $e->getMessage());
            return ['error' => 'Unable to delete translation'];
        }
    }
}
