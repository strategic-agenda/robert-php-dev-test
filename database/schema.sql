-- Implement the database schema as discussed in the design.

--This is the documents table and it is used to store the metadata of the original document that
-- is being translated

--Field explanation
-- id => auto-incremented ID of the document
-- name => Name of the uploaded document
-- source_language => ISO code of the original language (e.g., "en")
-- target_language => ISO code of the language to translate into
-- created_at => TIMESTAMP	Automatically set when the document is created

CREATE TABLE documents (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  source_language VARCHAR(10),
  target_language VARCHAR(10),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



--This is the translation_units table and it is used
-- to store individual translation segments from each document.
-- Field explanation 
-- id => Primary key, 
-- document_id => Foreign key to documents table,
-- unit_index => Sequential number of the unit in the document and ensures order is preserved in the document, 
-- source_text => Original segment from the source language,
-- target_text => Translated version, 
-- version => Current version number. Incremented when target text is edited,
-- created_at => When this translation unit was first created,
-- updated_at => Automatically updated when target_text is updated
CREATE TABLE translation_units (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  document_id BIGINT,
  unit_index INT,
  source_text TEXT,
  target_text TEXT,
  version INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (document_id) REFERENCES documents(id)
);

-- This is the translation_unit_versions table and it is used 
-- to track historical changes to translations for audit and rollback purposes
-- Field explanation 
-- id => Primary key,
-- translation_unit_id	=> Foreign key to translation_units table
-- target_text	=> Previous translation version
-- version => Corresponds to the version number from translation_units
-- updated_at => When this version was created
-- translated_by => Username or translator ID
-- notes => Optional notes about changes
CREATE TABLE translation_unit_versions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  translation_unit_id BIGINT,
  target_text TEXT,
  version INT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  translated_by	VARCHAR(255),
  notes	TEXT,
  FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id)
);