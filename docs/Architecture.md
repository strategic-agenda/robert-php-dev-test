# Task 1: Design Patterns and Architecture
# Solution design by Kunal Hari, [hari.kunal@gmail.com]

### 1. Handling Multilingual Content

Locale based Segmentation: Store each document with a source_locale and allow multiple target_locales. Use Laravel’s Localization features (https://laravel.com/docs/12.x/localization) for UI strings

Segmentation Engine: Implement a Segmenter interface with concrete classes (e.g., SentenceSegmenter, ParagraphSegmenter, XmlSegmenter). Use a Strategy Pattern to pick the correct segmenter based on input type

Storage: Central translation_units table capturing source_text, source_locale, target_locale, and segmentation metadata (e.g. segment_index).

### 2. Key Design Patterns

Repository Pattern: Abstract Eloquent queries behind TranslationUnitRepository and TranslationUnitVersionRepository to isolate data access logic

Service Layer: Create a TranslationService that orchestrates segmentation, translation updates, and version checks

Factory Pattern: SegmenterFactory to instantiate the appropriate segmenter for the uploaded document type

Observer/Event: Fire an event (TranslationUnitUpdated) on every translation update to trigger background tasks (e.g., QA checks, fuzzy-match suggestions)

Decorator Pattern: Wrap raw translation text in decorators (e.g., SpellcheckDecorator, GlossaryDecorator) for on-the-fly post-processing

### 3. Database Schema
-- translation_units
CREATE TABLE translation_units (
id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, -- Unique segment ID
document_id      BIGINT UNSIGNED NULL,                        -- FK: parent document (optional for now)
segment_index    INT UNSIGNED NOT NULL,                       -- Position in document (1-based)
source_text      TEXT NOT NULL,                              -- Original content to translate
source_locale    VARCHAR(5) NOT NULL,                        -- BCP-47 language code (e.g., 'en', 'fr')
target_locale    VARCHAR(5) NOT NULL,                        -- BCP-47 language code for translation
source_checksum  CHAR(32) GENERATED ALWAYS AS (MD5(source_text)) STORED, -- Hash for change detection
created_at       TIMESTAMP NULL,                             -- Created timestamp
updated_at       TIMESTAMP NULL                              -- Updated timestamp
);

-- translation_unit_versions
CREATE TABLE translation_unit_versions (
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
translation_unit_id BIGINT UNSIGNED NOT NULL REFERENCES translation_units(id) ON DELETE CASCADE,
translated_text     TEXT NOT NULL,                          -- Translated content
edited_by           BIGINT UNSIGNED NOT NULL,               -- User ID of editor
version_number      INT UNSIGNED NOT NULL,                  -- Incremental version counter
created_at          TIMESTAMP NULL                          -- Version saved timestamp
);

### 4. Version Control for Translations

1) Database Versioning: Each update inserts a new row into translation_unit_versions with incremented version_number

2) Git: Track code, schema migrations, and seeders in Git. Store translation source files (e.g. DOCX, XML) in a versioned archive location

3) API Endpoint: Provide /api/translation-units/{id}/versions to retrieve history
