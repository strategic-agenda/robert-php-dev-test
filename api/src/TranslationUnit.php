<?php

require_once __DIR__ . '/Database.php';

class TranslationUnit {
    private $id;
    private $sourceText;
    private $targetText;
    private $sourceLanguage;
    private $targetLanguage;
    private $createdAt;
    private $updatedAt;
    private $history = [];

    public function __construct($sourceText = null, $targetText = null, $sourceLanguage = null, $targetLanguage = null) {
        if ($sourceText !== null) {
            $this->sourceText = $sourceText;
            $this->targetText = $targetText;
            $this->sourceLanguage = $sourceLanguage;
            $this->targetLanguage = $targetLanguage;
            $this->createdAt = date('Y-m-d H:i:s');
            $this->updatedAt = date('Y-m-d H:i:s');
        }
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function getSourceText() {
        return $this->sourceText;
    }

    public function getTargetText() {
        return $this->targetText;
    }

    public function updateTargetText($newText) {
        // Add current state to history before updating
        $this->saveHistory();
        
        $this->targetText = $newText;
        $this->updatedAt = date('Y-m-d H:i:s');
        
        $this->update();
        
        return $this;
    }

    private function saveHistory() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO translation_history 
            (translation_unit_id, target_text, updated_at) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([
            $this->id,
            $this->targetText,
            $this->updatedAt
        ]);
    }

    public function getHistory() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT target_text, updated_at 
            FROM translation_history 
            WHERE translation_unit_id = ? 
            ORDER BY updated_at DESC
        ");
        
        $stmt->execute([$this->id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO translation_units 
            (source_text, target_text, source_language, target_language, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $this->sourceText,
            $this->targetText,
            $this->sourceLanguage,
            $this->targetLanguage,
            $this->createdAt,
            $this->updatedAt
        ]);
        
        $this->id = $db->lastInsertId();
        
        return $this;
    }

    public function update() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            UPDATE translation_units 
            SET target_text = ?, updated_at = ? 
            WHERE id = ?
        ");
        
        $stmt->execute([
            $this->targetText,
            $this->updatedAt,
            $this->id
        ]);
        
        return $this;
    }

    public static function findById($id) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT * FROM translation_units WHERE id = ?
        ");
        
        $stmt->execute([$id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }
        
        return self::createFromArray($data);
    }

    public static function findAll() {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->query("
            SELECT * FROM translation_units ORDER BY created_at DESC
        ");
        
        $units = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $units[] = self::createFromArray($row);
        }
        
        return $units;
    }

    private static function createFromArray($data) {
        $unit = new self();
        $unit->id = $data['id'];
        $unit->sourceText = $data['source_text'];
        $unit->targetText = $data['target_text'];
        $unit->sourceLanguage = $data['source_language'];
        $unit->targetLanguage = $data['target_language'];
        $unit->createdAt = $data['created_at'];
        $unit->updatedAt = $data['updated_at'];
        
        return $unit;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'sourceText' => $this->sourceText,
            'targetText' => $this->targetText,
            'sourceLanguage' => $this->sourceLanguage,
            'targetLanguage' => $this->targetLanguage,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'history' => $this->getHistory()
        ];
    }
} 