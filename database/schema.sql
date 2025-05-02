-- Database schema for Robert CAT Tool

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS translation_history;
DROP TABLE IF EXISTS project_translation_units;
DROP TABLE IF EXISTS translations;
DROP TABLE IF EXISTS translation_unit_history;
DROP TABLE IF EXISTS translation_units;
DROP TABLE IF EXISTS languages;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users
(
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)                                                 NOT NULL UNIQUE,
    email         VARCHAR(100)                                                NOT NULL UNIQUE,
    password_hash VARCHAR(255)                                                NOT NULL,
    first_name    VARCHAR(50),
    last_name     VARCHAR(50),
    role          ENUM ('admin', 'translator', 'reviewer', 'project_manager') NOT NULL DEFAULT 'translator',
    created_at    TIMESTAMP                                                            DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP                                                            DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login    TIMESTAMP                                                   NULL,
    status        ENUM ('active', 'inactive', 'suspended')                    NOT NULL DEFAULT 'active'
);

-- Create languages table
CREATE TABLE languages
(
    id      INT AUTO_INCREMENT PRIMARY KEY,
    code    VARCHAR(10) NOT NULL UNIQUE COMMENT 'ISO language code (e.g., en, fr, es)',
    name    VARCHAR(50) NOT NULL COMMENT 'Language name (e.g., English, French, Spanish)',
    is_rtl  BOOLEAN     NOT NULL DEFAULT FALSE COMMENT 'Right-to-left reading direction',
    enabled BOOLEAN     NOT NULL DEFAULT TRUE
);

-- Create translation_units table
CREATE TABLE translation_units
(
    id             INT AUTO_INCREMENT PRIMARY KEY,
    source_content TEXT                                      NOT NULL COMMENT 'Original content that needs translation',
    context        TEXT COMMENT 'Additional information to help translators',
    created_at     TIMESTAMP                                          DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP                                          DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by     INT,
    status         ENUM ('active', 'deprecated', 'archived') NOT NULL DEFAULT 'active',
    FOREIGN KEY (created_by) REFERENCES users (id)
);

-- Create translation_unit_history table
CREATE TABLE translation_unit_history
(
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id     INT  NOT NULL,
    previous_source_content TEXT NOT NULL,
    previous_context        TEXT,
    changed_at              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by              INT,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units (id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users (id)
);

-- Create translations table
CREATE TABLE translations
(
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id INT                                                    NOT NULL,
    language_id         INT                                                    NOT NULL,
    content             TEXT                                                   NOT NULL COMMENT 'Translated content',
    created_at          TIMESTAMP                                                       DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP                                                       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by          INT,
    status              ENUM ('draft', 'needs_review', 'approved', 'rejected') NOT NULL DEFAULT 'draft',
    revision_number     INT                                                    NOT NULL DEFAULT 1,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units (id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages (id),
    FOREIGN KEY (created_by) REFERENCES users (id),
    UNIQUE KEY (translation_unit_id, language_id) COMMENT 'Each unit can have only one translation per language'
);

-- Create translation_history table
CREATE TABLE translation_history
(
    id               INT AUTO_INCREMENT PRIMARY KEY,
    translation_id   INT  NOT NULL,
    previous_content TEXT NOT NULL,
    changed_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by       INT,
    revision_number  INT  NOT NULL,
    FOREIGN KEY (translation_id) REFERENCES translations (id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users (id)
);

-- Create indexes for better performance
CREATE INDEX idx_translations_language_id ON translations (language_id);
CREATE INDEX idx_translations_status ON translations (status);
CREATE INDEX idx_translation_units_status ON translation_units (status);

-- Insert default languages
INSERT INTO languages (code, name, is_rtl, enabled)
VALUES ('en', 'English', FALSE, TRUE),
       ('es', 'Spanish', FALSE, TRUE),
       ('fr', 'French', FALSE, TRUE),
       ('de', 'German', FALSE, TRUE),
       ('it', 'Italian', FALSE, TRUE),
       ('pt', 'Portuguese', FALSE, TRUE),
       ('ru', 'Russian', FALSE, TRUE),
       ('zh', 'Chinese', FALSE, TRUE),
       ('ja', 'Japanese', FALSE, TRUE),
       ('ar', 'Arabic', TRUE, TRUE),
       ('he', 'Hebrew', TRUE, TRUE);

-- Insert a test admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role)
VALUES ('admin', 'admin@example.com', '$2y$10$8zf0bvFUxHD.ZjBcG/4YruNpJT.0pJ.Z7e3jFldxGYx/nR1wMcAli', 'Admin', 'User',
        'admin');

-- Insert a test translator user (password: translator123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role)
VALUES ('translator', 'translator@example.com', '$2y$10$qKfJ0zToK9n7QF19Lz5speJ8w.n8DZ2xz7PxGX1nwbxx4kGW4.96G', 'Test',
        'Translator', 'translator');
