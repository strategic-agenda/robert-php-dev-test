-- Implement the database schema as discussed in the design.

-- Translation System Database Schema
-- This schema implements a complete translation management system with the following features:
-- 1. Translation unit management
-- 2. Translation history tracking
-- 3. User management with roles
-- 4. Project organization
-- 5. Multi-language support
-- 6. Soft delete functionality
-- 7. Automatic timestamp management

-- Create translation_units table
-- Stores the source text segments that need to be translated
-- Each unit represents a single translatable segment (sentence, phrase, etc.)
CREATE TABLE translation_units (
    id VARCHAR(36) PRIMARY KEY,                    -- UUID for unique identification
    source_text TEXT NOT NULL,                     -- Original text to be translated
    source_language VARCHAR(2) NOT NULL,           -- ISO 639-1 language code (e.g., 'en', 'es')
    target_language VARCHAR(2) NOT NULL,           -- Target language code
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,                     -- Soft delete timestamp
    INDEX idx_source_lang (source_language),       -- Index for language-based queries
    INDEX idx_target_lang (target_language),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create translations table
-- Stores the actual translations for each translation unit
-- Multiple translations can exist for a single unit (different versions)
CREATE TABLE translations (
    id VARCHAR(36) PRIMARY KEY,                    -- UUID for unique identification
    translation_unit_id VARCHAR(36) NOT NULL,      -- Reference to the source unit
    target_text TEXT NOT NULL,                     -- The translated text
    translator_id VARCHAR(36) NOT NULL,            -- User who created the translation
    status ENUM('draft', 'in_review', 'approved', 'rejected') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id),
    INDEX idx_translation_unit (translation_unit_id),
    INDEX idx_translator (translator_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create translation_history table
-- Tracks all changes made to translations
-- Maintains a complete audit trail of modifications
CREATE TABLE translation_history (
    id VARCHAR(36) PRIMARY KEY,                    -- UUID for unique identification
    translation_id VARCHAR(36) NOT NULL,           -- Reference to the translation
    previous_text TEXT NOT NULL,                   -- Previous version of the translation
    changed_by VARCHAR(36) NOT NULL,               -- User who made the change
    change_reason TEXT,                            -- Optional reason for the change
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_id) REFERENCES translations(id),
    INDEX idx_translation (translation_id),
    INDEX idx_changed_by (changed_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create users table
-- Stores user information and role-based access control
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,                    -- UUID for unique identification
    name VARCHAR(255) NOT NULL,                    -- User's full name
    email VARCHAR(255) NOT NULL UNIQUE,            -- Unique email address
    role ENUM('translator', 'reviewer', 'admin') NOT NULL DEFAULT 'translator',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,                     -- Soft delete timestamp
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create projects table
-- Manages translation projects and their configurations
CREATE TABLE projects (
    id VARCHAR(36) PRIMARY KEY,                    -- UUID for unique identification
    name VARCHAR(255) NOT NULL,                    -- Project name
    description TEXT,                              -- Project description
    source_language VARCHAR(2) NOT NULL,           -- Source language code
    target_languages JSON NOT NULL,                -- Array of target language codes
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,                     -- Soft delete timestamp
    INDEX idx_source_lang (source_language),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create project_translation_units table
-- Junction table for many-to-many relationship between projects and translation units
CREATE TABLE project_translation_units (
    project_id VARCHAR(36) NOT NULL,               -- Reference to project
    translation_unit_id VARCHAR(36) NOT NULL,      -- Reference to translation unit
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (project_id, translation_unit_id),
    FOREIGN KEY (project_id) REFERENCES projects(id),
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key constraints for users
ALTER TABLE translations
ADD CONSTRAINT fk_translator
FOREIGN KEY (translator_id) REFERENCES users(id);

ALTER TABLE translation_history
ADD CONSTRAINT fk_changed_by
FOREIGN KEY (changed_by) REFERENCES users(id);

-- Add triggers for updating updated_at timestamps
DELIMITER //

CREATE TRIGGER translation_units_update_timestamp
BEFORE UPDATE ON translation_units
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//

CREATE TRIGGER translations_update_timestamp
BEFORE UPDATE ON translations
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//

CREATE TRIGGER users_update_timestamp
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//

CREATE TRIGGER projects_update_timestamp
BEFORE UPDATE ON projects
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END//

DELIMITER ;

-- Sample Data
-- Insert sample users
INSERT INTO users (id, name, email, role) VALUES
('u1', 'John Doe', 'john@example.com', 'translator'),
('u2', 'Jane Smith', 'jane@example.com', 'reviewer'),
('u3', 'Admin User', 'admin@example.com', 'admin');

-- Insert sample project
INSERT INTO projects (id, name, description, source_language, target_languages) VALUES
('p1', 'Website Localization', 'Main website translation project', 'en', '["es", "fr", "de"]');

-- Insert sample translation units
INSERT INTO translation_units (id, source_text, source_language, target_language) VALUES
('tu1', 'Welcome to our website!', 'en', 'es'),
('tu2', 'Please contact us for more information.', 'en', 'es'),
('tu3', 'Thank you for your interest.', 'en', 'es');

-- Link translation units to project
INSERT INTO project_translation_units (project_id, translation_unit_id) VALUES
('p1', 'tu1'),
('p1', 'tu2'),
('p1', 'tu3');

-- Insert sample translations
INSERT INTO translations (id, translation_unit_id, target_text, translator_id, status) VALUES
('t1', 'tu1', '¡Bienvenido a nuestro sitio web!', 'u1', 'approved'),
('t2', 'tu2', 'Por favor contáctenos para más información.', 'u1', 'in_review'),
('t3', 'tu3', 'Gracias por su interés.', 'u1', 'draft');

-- Insert sample translation history
INSERT INTO translation_history (id, translation_id, previous_text, changed_by, change_reason) VALUES
('th1', 't1', 'Bienvenido a nuestro sitio web', 'u1', 'Added exclamation mark'),
('th2', 't2', 'Contáctenos para más información', 'u1', 'Added "Por favor" for politeness');
