<?php

class TranslationUnit
{
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all the translation units
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->query("
                SELECT tu.*, l.name as source_language_name 
                FROM translation_units as tu
                INNER JOIN languages as l on tu.source_language_id = l.id
                ORDER BY tu.id DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching translation units: " . $e->getMessage());
            return ['error' => 'Unable to fetch translation units'];
        }
    }

    /**
     * Get the translation units by given id
     */
    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM translation_units WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching translation unit by ID: " . $e->getMessage());
            return ['error' => 'Translation Unit not found'];
        }
    }

    /**
     * Create the translation unit based on given source and language
     */
    public function create($source, $sourceLanguageId) {
        try {
            $stmt = $this->db->prepare("INSERT INTO translation_units (source, source_language_id) VALUES (?, ?)");
            $stmt->execute([$source, $sourceLanguageId]);

            return ['message' => 'Translation Unit has been created.', 'id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Error creating translation unit: " . $e->getMessage());
            return ['error' => 'Unable to create translation unit'];
        }
    }

    /**
     * Update the translation unit based on given source and language
     */
    public function update($id, $newSource, $sourceLanguageId) {
        try {
            $stmt = $this->db->prepare("SELECT source, source_language_id FROM translation_units WHERE id = ?");
            $stmt->execute([$id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$current) {
                return ['error' => 'Translation Unit not found'];
            }
    
            $historyStmt = $this->db->prepare("
                INSERT INTO translation_unit_history (translation_unit_id, source, source_language_id)
                VALUES (?, ?, ?)
            ");
            $historyStmt->execute([$id, $current['source'], $current['source_language_id']]);
    
            $updateStmt = $this->db->prepare("
                UPDATE translation_units SET source = ?, source_language_id = ? WHERE id = ?
            ");
            $updateStmt->execute([$newSource, $sourceLanguageId, $id]);
    
            return ['message' => 'Translation Unit has been updated and history stored.'];
        } catch (PDOException $e) {
            error_log("Error updating translation unit with history: " . $e->getMessage());
            return ['error' => 'Unable to update translation unit'];
        }
    }    

    /**
     * Delete the translation unit based on given id
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM translation_history WHERE translation_unit_id = ?");
            $stmt->execute([$id]);
            
            $stmt = $this->db->prepare("DELETE FROM translations WHERE translation_unit_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM translation_unit_history WHERE translation_unit_id = ?");
            $stmt->execute([$id]);

            $stmt = $this->db->prepare("DELETE FROM translation_units WHERE id = ?");
            $stmt->execute([$id]);

            return ['message' => 'Translation Unit and associated translations have been deleted.'];
        } catch (PDOException $e) {
            error_log("Error deleting translation unit: " . $e->getMessage());
            return ['error' => 'Unable to delete translation unit'];
        }
    }
}
