-- Languages
CREATE TABLE languages (
    id SERIAL PRIMARY KEY,
    code VARCHAR(5) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
);

-- Translation Units
CREATE TABLE translation_units (
    id SERIAL PRIMARY KEY,
    source_text TEXT NOT NULL,
    source_language_id INTEGER NOT NULL REFERENCES languages(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Translations
CREATE TABLE translations (
    id SERIAL PRIMARY KEY,
    unit_id INTEGER NOT NULL REFERENCES translation_units(id) ON DELETE CASCADE,
    target_language_id INTEGER NOT NULL REFERENCES languages(id),
    translated_text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL,
    UNIQUE (unit_id, target_language_id)
);

-- Version History
CREATE TABLE translation_versions (
    id SERIAL PRIMARY KEY,
    translation_id INTEGER NOT NULL REFERENCES translations(id) ON DELETE CASCADE,
    translated_text TEXT NOT NULL,
    version INTEGER NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_units_source_language ON translation_units(source_language_id);
CREATE INDEX idx_translations_unit ON translations(unit_id);
CREATE INDEX idx_translations_language ON translations(target_language_id);
CREATE INDEX idx_versions_translation ON translation_versions(translation_id);
