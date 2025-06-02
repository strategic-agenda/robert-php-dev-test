-- languages table
CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL,
    name VARCHAR(50) NOT NULL
);

-- source texts table
CREATE TABLE IF NOT EXISTS translation_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_text TEXT NOT NULL,
    source_lang_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (source_lang_id) REFERENCES languages(id)
);

-- traslations table
CREATE TABLE IF NOT EXISTS translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    target_lang_id INT NOT NULL,
    translated_text TEXT NOT NULL,
    version INT DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES translation_units(id),
    FOREIGN KEY (target_lang_id) REFERENCES languages(id)
);

-- history table
CREATE TABLE IF NOT EXISTS translation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_id INT NOT NULL,
    previous_text TEXT NOT NULL,
    version INT NOT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_id) REFERENCES translations(id)
);
