-- Implement the database schema as discussed in the design.
CREATE TABLE IF NOT EXISTS translation_units (
    id VARCHAR(255) PRIMARY KEY,
    source TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);
CREATE TABLE IF NOT EXISTS translations (
    unit_id VARCHAR(255),
    lang_code VARCHAR(10),
    text TEXT NOT NULL,
    PRIMARY KEY (unit_id, lang_code),
    FOREIGN KEY (unit_id) REFERENCES translation_units(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS translation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id VARCHAR(255),
    lang_code VARCHAR(10),
    old_text TEXT,
    new_text TEXT,
    timestamp DATETIME NOT NULL,
    FOREIGN KEY (unit_id) REFERENCES translation_units(id) ON DELETE CASCADE
);
