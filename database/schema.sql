-- Implement the database schema as discussed in the design.

-- Languages table
CREATE TABLE languages (
    code VARCHAR(10) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    direction ENUM('ltr', 'rtl') NOT NULL DEFAULT 'ltr',
    active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert common languages
INSERT INTO languages (code, name) VALUES
('en', 'English'),
('fr', 'French'),
('de', 'German'),
('es', 'Spanish'),
('it', 'Italian'),
('zh', 'Chinese'),
('ja', 'Japanese'),
('ru', 'Russian'),
('ar', 'Arabic');

-- Translation units table with foreign keys to languages
CREATE TABLE translation_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_text TEXT NOT NULL,
    target_text TEXT,
    source_language VARCHAR(10) NOT NULL,
    target_language VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_language) REFERENCES languages(code),
    FOREIGN KEY (target_language) REFERENCES languages(code)
);

-- Translation history table
CREATE TABLE translation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id INT NOT NULL,
    target_text TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id)
);
