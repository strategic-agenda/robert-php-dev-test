-- Implement the database schema as discussed in the design.

CREATE TABLE translation_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_text TEXT NOT NULL,
    translated_text TEXT,
    language_from VARCHAR(10) NOT NULL,
    language_to VARCHAR(10) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE translation_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  translation_unit_id INT NOT NULL,
  old_source_text TEXT NOT NULL,
  new_source_text TEXT NOT NULL,
  old_translated_text TEXT NOT NULL,
  new_translated_text TEXT NOT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (translation_unit_id) REFERENCES translations(id) ON DELETE CASCADE
);