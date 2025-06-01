-- Implement the database schema as discussed in the design.

-- 1. Store metadata about each uploaded document
CREATE TABLE documents (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    title         TEXT        NOT NULL,
    language      VARCHAR(10) NOT NULL,       -- e.g. "en", "fr", "zh"
    uploaded_at   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    file_path     TEXT        NOT NULL          -- e.g. "uploads/doc123.txt"
);

-- 2. Store each translation unit, linked to a document
CREATE TABLE translation_units (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    document_id      INTEGER    NOT NULL,
    sequence_number  INTEGER    NOT NULL,      
    source_content   TEXT       NOT NULL,
    is_locked        BOOLEAN    NOT NULL DEFAULT 0,
    created_at       DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_document
       FOREIGN KEY (document_id) REFERENCES documents(id)
         ON DELETE CASCADE
);

-- 3. Store translations for each unit
CREATE TABLE translations (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    translation_unit_id INTEGER    NOT NULL,
    target_language     VARCHAR(10)NOT NULL,   -- e.g. "fr", "es"
    translated_content  TEXT       NOT NULL,
    version             INTEGER    NOT NULL DEFAULT 1,
    created_at          DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by_user     VARCHAR(100),           
    change_comment      TEXT,                   -- reason for changes
    CONSTRAINT fk_unit
       FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id)
         ON DELETE CASCADE,
    CONSTRAINT unique_unit_language_version
       UNIQUE (translation_unit_id, target_language, version)
);

-- 4. Audit log table for a full history of changes
CREATE TABLE translation_audit_log (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    translation_id      INTEGER    NOT NULL,
    previous_content    TEXT,
    new_content         TEXT,
    changed_at          DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    changed_by_user     VARCHAR(100),
    change_reason       TEXT,
    CONSTRAINT fk_translation
       FOREIGN KEY (translation_id) REFERENCES translations(id)
         ON DELETE CASCADE
);
