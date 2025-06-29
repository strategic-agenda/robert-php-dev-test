<?php
namespace CAT;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;

    public function __construct(
        string $host,
        string $dbname,
        string $user,
        string $pass,
        int $port = 5432
    ) {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \Exception('Database connection failed.');
            exit;
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function initializeSchema(): void
    {
        // this can be a file returning an array for example..
        $queries = [
            "CREATE TABLE IF NOT EXISTS languages (
                id SERIAL PRIMARY KEY,
                code VARCHAR(5) NOT NULL UNIQUE,
                name VARCHAR(50) NOT NULL
            )",
            "CREATE TABLE IF NOT EXISTS translation_units (
                id SERIAL PRIMARY KEY,
                source_text TEXT NOT NULL,
                source_language_id INTEGER NOT NULL REFERENCES languages(id),
                context TEXT,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE TABLE IF NOT EXISTS translations (
                id SERIAL PRIMARY KEY,
                unit_id INTEGER NOT NULL REFERENCES translation_units(id) ON DELETE CASCADE,
                target_language_id INTEGER NOT NULL REFERENCES languages(id),
                translated_text TEXT NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL,
                UNIQUE (unit_id, target_language_id)
            )",
            "CREATE TABLE IF NOT EXISTS translation_versions (
                id SERIAL PRIMARY KEY,
                translation_id INTEGER NOT NULL REFERENCES translations(id) ON DELETE CASCADE,
                translated_text TEXT NOT NULL,
                version INTEGER NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            )",
            "CREATE INDEX IF NOT EXISTS idx_units_source_language ON translation_units(source_language_id)",
            "CREATE INDEX IF NOT EXISTS idx_translations_unit ON translations(unit_id)",
            "CREATE INDEX IF NOT EXISTS idx_translations_language ON translations(target_language_id)",
            "CREATE INDEX IF NOT EXISTS idx_versions_translation ON translation_versions(translation_id)"
        ];

        foreach ($queries as $sql) {
            $this->pdo->exec($sql);
        }

        // seed data
        $stmt = $this->pdo->prepare("
            INSERT INTO languages (code, name)
            VALUES (:code, :name)
            ON CONFLICT (code) DO NOTHING
        ");

        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'es', 'name' => 'Español'],
        ];

        foreach ($languages as $lang) {
            $stmt->execute([
                ':code' => $lang['code'],
                ':name' => $lang['name'],
            ]);
        }
    }
}
