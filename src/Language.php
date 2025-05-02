<?php

namespace Robert\CAT;

class Language
{
    private $db;

    /**
     * Constructor with dependency injection for database connection
     *
     * @param \PDO $db Database connection
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get a language by ID
     *
     * @param int $id Language ID
     * @return array|null Language data or null if not found
     */
    public function getLanguageById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM languages WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $language = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$language) {
            return null;
        }

        return $language;
    }

    /**
     * Get all languages with optional filtering
     *
     * @param string $search Optional search term for name or code
     * @param bool|null $enabled Optional filter for enabled status
     * @return array Array of languages matching the criteria
     */
    public function getLanguages(string $search = '', ?bool $enabled = null): array
    {
        $whereConditions = [];
        $params = [];

        // Add search condition if provided
        if (!empty($search)) {
            $whereConditions[] = "(name LIKE :searchName OR code LIKE :searchCode)";
            $params[':searchName'] = "%$search%";
            $params[':searchCode'] = "%$search%";
        }

        // Add enabled filter if provided
        if ($enabled !== null) {
            $whereConditions[] = "enabled = :enabled";
            $params[':enabled'] = $enabled;
        }

        // Build WHERE clause
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $query = "
            SELECT l.*, 
                  (SELECT COUNT(*) FROM translations WHERE language_id = l.id) AS translations_count
            FROM languages l
            $whereClause
            ORDER BY l.name ASC
        ";

        $stmt = $this->db->prepare($query);

        // Bind parameters
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $stmt->bindValue($key, $value, \PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Count languages matching specific criteria
     *
     * @param string $search Optional search term
     * @param bool|null $enabled Optional enabled status
     * @return int Number of matching languages
     */
    public function countLanguages(string $search = '', ?bool $enabled = null): int
    {
        $whereConditions = [];
        $params = [];

        // Add search condition if provided
        if (!empty($search)) {
            $whereConditions[] = "(name LIKE :searchName OR code LIKE :searchCode)";
            $params[':searchName'] = "%$search%";
            $params[':searchCode'] = "%$search%";
        }

        // Add enabled filter if provided
        if ($enabled !== null) {
            $whereConditions[] = "enabled = :enabled";
            $params[':enabled'] = $enabled;
        }

        // Build WHERE clause
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
        }

        $query = "SELECT COUNT(*) FROM languages $whereClause";


        $stmt = $this->db->prepare($query);

        // Bind parameters
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $stmt->bindValue($key, $value, \PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Check if a language code already exists
     *
     * @param string $code Language code to check
     * @param int|null $excludeId Optional ID to exclude from the check (for updates)
     * @return bool True if the code exists
     */
    public function languageCodeExists(string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM languages WHERE code = :code";
        $params = [':code' => $code];

        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return (bool)$stmt->fetch();
    }

    /**
     * Add a new language
     *
     * @param string $code Language code
     * @param string $name Language name
     * @param bool $isRtl Whether the language is right-to-left
     * @param bool $enabled Whether the language is enabled
     * @return int ID of the newly created language
     */
    public function addLanguage(string $code, string $name, bool $isRtl = false, bool $enabled = true): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO languages (code, name, is_rtl, enabled)
            VALUES (:code, :name, :is_rtl, :enabled)
        ");

        $stmt->bindParam(':code', $code, \PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->bindParam(':is_rtl', $isRtl, \PDO::PARAM_BOOL);
        $stmt->bindParam(':enabled', $enabled, \PDO::PARAM_BOOL);

        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing language
     *
     * @param int $id Language ID
     * @param array $data Array of fields to update
     * @return bool True if update was successful
     */
    public function updateLanguage(int $id, array $data): bool
    {
        // Build update SQL dynamically based on provided fields
        $updateFields = [];
        $params = [];

        if (isset($data['name'])) {
            $updateFields[] = "name = :name";
            $params[':name'] = $data['name'];
        }

        if (isset($data['code'])) {
            $updateFields[] = "code = :code";
            $params[':code'] = $data['code'];
        }

        if (isset($data['is_rtl'])) {
            $updateFields[] = "is_rtl = :is_rtl";
            $params[':is_rtl'] = (bool)$data['is_rtl'];
        }

        if (isset($data['enabled'])) {
            $updateFields[] = "enabled = :enabled";
            $params[':enabled'] = (bool)$data['enabled'];
        }

        if (empty($updateFields)) {
            return false;
        }

        $params[':id'] = $id;

        // Prepare and execute update statement
        $sql = "UPDATE languages SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $stmt->bindValue($key, $value, \PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        return $stmt->execute();
    }

    /**
     * Count translations for a language
     *
     * @param int $languageId Language ID
     * @return int Number of translations
     */
    public function countTranslations(int $languageId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM translations WHERE language_id = :id");
        $stmt->bindParam(':id', $languageId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Disable a language
     *
     * @param int $id Language ID
     * @return bool True if the operation was successful
     */
    public function disableLanguage(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE languages SET enabled = FALSE WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Delete a language
     *
     * @param int $id Language ID
     * @return bool True if the operation was successful
     */
    public function deleteLanguage(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM languages WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

        return $stmt->execute();
    }
}
