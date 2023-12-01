<?php

namespace models;

use PDOException;
use PDO;
use models\TranslationHistory;

// Model for translation_units DB table
class TranslationUnit extends Model
{
    // Handle insert or update operation
    public function save($data, $id = null)
    {
        $history = new TranslationHistory();

        if ($id) {
            $sql = 
                "UPDATE translation_units 
                SET unit_text = :unit_text, 
                    unit_type = :unit_type, 
                    lang_code = :lang_code, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        } else {
            $sql = 
                "INSERT INTO translation_units (unit_text, unit_type, lang_code, created_at, updated_at) 
                VALUES (:unit_text, :unit_type, :lang_code, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $stmt = $this->db->prepare($sql);
        }

        $stmt->bindParam(':unit_text', $data['text']);
        $stmt->bindParam(':unit_type', $data['type']);
        $stmt->bindParam(':lang_code', $data['languageCode']);

        if ($id) {
            $history->save($data, $id);
        }
        
        $stmt->execute();
        
        return json_encode([
            'status' => 200,
            'message' => 'Translation unit saved successfully'
        ]);
    }

    // Return list of all Translation Unit's in table
    public function list()
    {
        $stmt = $this->db->query("SELECT * FROM translation_units ORDER BY created_at DESC");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return json_encode($result);
    }

    // Find Translation unit by id
    public function get($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM translation_units WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_encode($result);
    }

    // Delete Translation unit by id
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM translation_units WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return json_encode([
            'status' => 200,
            'message' => 'Translation unit deleted successfully'
        ]);
    }
}