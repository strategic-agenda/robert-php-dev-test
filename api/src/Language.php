<?php

require_once __DIR__ . '/Database.php';

class Language {
    private $code;
    private $name;
    private $direction;
    private $active;
    private $createdAt;

    public function __construct($code = null, $name = null, $direction = 'ltr', $active = true) {
        if ($code !== null) {
            $this->code = $code;
            $this->name = $name;
            $this->direction = $direction;
            $this->active = $active;
            $this->createdAt = date('Y-m-d H:i:s');
        }
    }

    public function getCode() {
        return $this->code;
    }

    public function getName() {
        return $this->name;
    }

    public function getDirection() {
        return $this->direction;
    }

    public function isActive() {
        return $this->active;
    }

    public static function findByCode($code) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT * FROM languages WHERE code = ?
        ");
        
        $stmt->execute([$code]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            return null;
        }
        
        return self::createFromArray($data);
    }

    public static function findAll($activeOnly = true) {
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT * FROM languages";
        if ($activeOnly) {
            $sql .= " WHERE active = 1";
        }
        $sql .= " ORDER BY name";
        
        $stmt = $db->query($sql);
        
        $languages = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $languages[] = self::createFromArray($row);
        }
        
        return $languages;
    }

    private static function createFromArray($data) {
        $language = new self();
        $language->code = $data['code'];
        $language->name = $data['name'];
        $language->direction = $data['direction'];
        $language->active = (bool)$data['active'];
        $language->createdAt = $data['created_at'];
        
        return $language;
    }

    public function toArray() {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'direction' => $this->direction,
            'active' => $this->active,
            'createdAt' => $this->createdAt
        ];
    }
} 