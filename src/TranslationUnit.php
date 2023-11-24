<?php

namespace AwaisAmir\RobertPhpDevTest;

require_once '../vendor/autoload.php';
use AwaisAmir\RobertPhpDevTest\Config\Database;

class TranslationUnit
{
    private $conn;

    public function __construct() {
        // Create a database connection
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function addTranslationUnit($text) {
        $text = $this->conn->real_escape_string($text);
        $query = $this->conn->prepare("INSERT INTO translation_units (text) VALUES (?)");
        $query->execute([$text]);
        return $this->getTranslationUnitById((int)$this->conn->insert_id);
    }

    public function getTranslationUnitById($id) {
        $query = $this->conn->prepare("SELECT * FROM translation_units WHERE id = ?");
        $query->execute([$id]);

        $result = $query->get_result(); // Get the result set
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return null; // Translation unit not found
        }
    }

    public function updateTranslationUnit($id, $newText) {
        $query = $this->conn->prepare("SELECT * FROM translation_units WHERE id = ?");
        $query->execute([$id]);
        $result = $query->get_result(); // Get the result set
        $row = [];
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        }

        if(count($row)) {
            $query = $this->conn->prepare(
                "INSERT INTO translations_histories (translation_unit_id, translated_text) VALUES (?, ?)"
            );
            $query->execute([$id, $row['text']]);

            $newText = $this->conn->real_escape_string($newText);
            $query = $this->conn->prepare("UPDATE translation_units SET text = ? WHERE id = ?");
            $query->execute([$newText, $id]);
            return $this->getTranslationUnitById((int)$id);
        }

        return 'Translation unit not found.';
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function deleteTranslationUnit($id) {
        $query = $this->conn->prepare("DELETE FROM translation_units WHERE id = ?");
        $query->execute([$id]);
    }

    public function getAllTranslationUnits() {
        $query = $this->conn->query("SELECT * FROM translation_units");
        $rows = [];

        while ($row = $query->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
/*
$translationManager = new TranslationUnit();

// Add new translation units
$row = $translationManager->addTranslationUnit("Hello, World!");
$translationManager->addTranslationUnit("This is a sample translation.");

// Retrieve a translation unit by ID
$translation = $translationManager->getTranslationUnitById($row['id']);
echo "Translation Unit 1: ". $translation['text'] . '<br>';

// Update a translation unit
$translationManager->updateTranslationUnit($row['id'], "Bonjour, le Monde!");
$translation = $translationManager->getTranslationUnitById($row['id']);
echo "Updated Translation Unit 1: ". $translation['text'] . '<br>';

// Close the database connection when done
$translationManager->closeConnection();
*/
