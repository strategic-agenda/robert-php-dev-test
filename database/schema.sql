-- Implement the database schema as discussed in the design.
CREATE DATABASE IF NOT EXISTS translation_db;
USE translation_db;

CREATE TABLE languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(10) NOT NULL
);

CREATE TABLE translation_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_text TEXT NOT NULL,
    language_id INT NOT NULL,
    FOREIGN KEY (language_id) REFERENCES languages(id)
);

CREATE TABLE translation_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id INT NOT NULL,
    translated_text TEXT NOT NULL,
    version INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id)
);

INSERT INTO `translation_db`.`languages` (`id`, `name`, `code`) VALUES (1, 'English', 'EN');
