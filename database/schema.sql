-- Implement the database schema as discussed in the design.
CREATE TABLE translation_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source TEXT NOT NULL,
    source_language VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    target_language VARCHAR(10) NOT NULL,
    translated TEXT NOT NULL,
    version INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES translation_units(id) ON DELETE CASCADE
);
CREATE TABLE translation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_id INT NOT NULL,
    translated TEXT NOT NULL,
    version INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_id) REFERENCES translations(id) ON DELETE CASCADE
);