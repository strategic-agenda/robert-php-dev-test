<?php

class Language
{
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Get all the languages
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM languages");

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching languages: " . $e->getMessage());
            return ['error' => 'Unable to fetch languages'];
        }
    }
}
