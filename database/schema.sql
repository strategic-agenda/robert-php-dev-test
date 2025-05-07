-- Implement the database schema as discussed in the design.

CREATE TABLE translation_units (
    id SERIAL PRIMARY KEY,
    source_text TEXT NOT NULL,
    target_text TEXT NOT NULL,
    source_language VARCHAR(10) NOT NULL,
    target_language VARCHAR(10) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE translation_history (
    id SERIAL PRIMARY KEY,
    unit_id INTEGER NOT NULL REFERENCES translation_units(id) ON DELETE CASCADE,
    target_text TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_translation_units_languages ON translation_units(source_language, target_language);
CREATE INDEX idx_translation_history_unit_id ON translation_history(unit_id);
