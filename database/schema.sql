CREATE DATABASE IF NOT EXISTS `robert-cat-tool` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `robert-cat-tool`;

CREATE TABLE languages (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(10) NOT NULL
);


INSERT INTO languages (name, code)
VALUES ('English', 'en'),
       ('Spanish', 'es'),
       ('French', 'fr'),
       ('German', 'de');


CREATE TABLE translation_units (
  id INT PRIMARY KEY AUTO_INCREMENT,
  source TEXT NOT NULL,
  source_language_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (source_language_id) REFERENCES languages(id)
);



CREATE TABLE translation_unit_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id INT NOT NULL,
    source TEXT NOT NULL,
    source_language_id INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id) ON DELETE CASCADE,
    FOREIGN KEY (source_language_id) REFERENCES languages(id)
);



CREATE TABLE translations (
  id INT PRIMARY KEY AUTO_INCREMENT,
  translation_unit_id INT NOT NULL,
  translated_text TEXT NOT NULL,
  translated_language_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id),
  FOREIGN KEY (translated_language_id) REFERENCES languages(id)
);


CREATE TABLE translation_history (
  id INT PRIMARY KEY AUTO_INCREMENT,
  translation_id INT NOT NULL,
  translation_unit_id INT NOT NULL,
  translated_text TEXT NOT NULL,
  translated_language_id INT NOT NULL,
  version INT NOT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (translation_id) REFERENCES translations(id),
  FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id),
  FOREIGN KEY (translated_language_id) REFERENCES languages(id)
);

