-- Database schema for the Computer-Assisted Translation (CAT) Tool
-- Designed to store and manage translation units, translations, version history, projects, and users

-- Create the database (optional, if not already created)
CREATE DATABASE IF NOT EXISTS cat_tool;
USE cat_tool;

-- Table for projects
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    source_language VARCHAR(2) NOT NULL COMMENT 'ISO 639-1 language code (e.g., "en")',
    target_languages JSON NOT NULL COMMENT 'Array of target language codes (e.g., ["es", "fr"])',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_source_language (source_language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for users
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('translator', 'admin') NOT NULL DEFAULT 'translator',
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for translation units
CREATE TABLE translation_units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_text TEXT NOT NULL COMMENT 'Original text segment (e.g., sentence or phrase)',
    source_language VARCHAR(2) NOT NULL COMMENT 'ISO 639-1 language code (e.g., "en")',
    project_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_source_language (source_language),
    INDEX idx_project_id (project_id),
    FULLTEXT idx_source_text (source_text)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for translations
CREATE TABLE translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id BIGINT UNSIGNED NOT NULL,
    target_text TEXT NOT NULL COMMENT 'Translated text',
    target_language VARCHAR(2) NOT NULL COMMENT 'ISO 639-1 language code (e.g., "es")',
    translator_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'approved', 'rejected') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL COMMENT 'Soft delete timestamp',
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id) ON DELETE CASCADE,
    FOREIGN KEY (translator_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_translation_unit_id (translation_unit_id),
    INDEX idx_target_language (target_language),
    INDEX idx_translator_id (translator_id),
    FULLTEXT idx_target_text (target_text)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for translation history
CREATE TABLE translation_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    translation_id BIGINT UNSIGNED NOT NULL,
    previous_text TEXT NOT NULL COMMENT 'Previous version of the translated text',
    changed_by BIGINT UNSIGNED NOT NULL,
    change_reason TEXT NULL COMMENT 'Optional reason for the change',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_id) REFERENCES translations(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_translation_id (translation_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
    PARTITION p0 VALUES LESS THAN (UNIX_TIMESTAMP('2025-01-01 00:00:00')),
    PARTITION p1 VALUES LESS THAN (UNIX_TIMESTAMP('2026-01-01 00:00:00')),
    PARTITION p2 VALUES LESS THAN (MAXVALUE)
);

-- Add comments to describe the schema
COMMENT ON TABLE projects IS 'Stores translation projects with source and target languages';
COMMENT ON TABLE users IS 'Stores user information including translators and admins';
COMMENT ON TABLE translation_units IS 'Stores source text segments (translation units) for translation';
COMMENT ON TABLE translations IS 'Stores translated texts for each translation unit';
COMMENT ON TABLE translation_history IS 'Stores version history of translation changes';

-- Example data for testing
INSERT INTO projects (name, source_language, target_languages) VALUES
    ('Website Localization', 'en', '["es", "fr", "de"]');

INSERT INTO users (name, email, role, password) VALUES
    ('John Doe', 'john.doe@example.com', 'translator', '$2y$10$...hashed_password...'),
    ('Jane Smith', 'jane.smith@example.com', 'admin', '$2y$10$...hashed_password...');

INSERT INTO translation_units (source_text, source_language, project_id) VALUES
    ('Hello, world!', 'en', 1);

INSERT INTO translations (translation_unit_id, target_text, target_language, translator_id, status) VALUES
    (1, '¡Hola, mundo!', 'es', 1, 'draft'),
    (1, 'Bonjour le monde!', 'fr', 1, 'approved');

INSERT INTO translation_history (translation_id, previous_text, changed_by, change_reason, created_at) VALUES
    (1, 'Hola mundo', 1, 'Improved translation accuracy', '2025-05-01 10:00:00');