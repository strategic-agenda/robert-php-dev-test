CREATE DATABASE IF NOT EXISTS robert_translation;
USE robert_translation;

CREATE TABLE languages (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `label` VARCHAR(255) NOT NULL,
    `code` VARCHAR(10) NOT NULL
);

CREATE TABLE translation_units (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unit_text` TEXT NOT NULL,
    `translated_text` TEXT,
    `language_id` INT,
    `translation_version` INT DEFAULT 1,
    FOREIGN KEY (language_id) REFERENCES languages(id) 
);

CREATE TABLE translation_unit_records (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `translation_unit_id` INT NOT NULL,
    `translation_version` INT NOT NULL,
    `translated_text` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id) ON DELETE CASCADE
);

CREATE INDEX translation_unit_records_versions ON translation_unit_records (translation_unit_id , translation_version)

INSERT INTO `languages` (label , code) VALUES (English , en)