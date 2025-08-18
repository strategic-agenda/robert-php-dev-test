# Explaining the architecture and design decisions

# Robert CAT Tool – Architecture

## 🏗️ Folder Structure

- `src/`: Core business logic (PHP classes)
- `api/`: REST API endpoints
- `database/`: Database schema and setup scripts
- `frontend/`: ReactJS frontend application
- `tests/`: PHPUnit test cases
- `docs/`: Documentation and architecture files

---

## 🎯 Design Patterns

- **Repository Pattern**: Separates data persistence from business logic
- **Service Layer**: Centralizes application logic and operations
- **DTOs** (optional): For clean data transfer between layers

---

## 🌍 Multilingual Support

Each translation unit will contain:
- `source_text`: Original text
- `translated_text`: Translated version
- `language_from`: Source language (e.g., "en")
- `language_to`: Target language (e.g., "pt")

---

## 💾 Version Control of Translations

Whenever a translation is updated:
- The previous version is stored in the `translation_unit_versions` table
- This allows tracking the full translation history over time

---

## 🧱 Database Schema (see `database/schema.sql`)

### Main Tables:

#### `translation_units`
- `id`: Unique identifier
- `source_text`: Original content
- `translated_text`: Translated content
- `language_from`: Source language code
- `language_to`: Target language code
- `created_at`: Creation timestamp
- `updated_at`: Last modification timestamp

#### `translation_unit_versions`
- `id`: Unique identifier
- `translation_unit_id`: Reference to the original translation
- `old_source_text`: Previous source content
- `new_source_text`: New source content
- `old_translated_text`: Previous translated content
- `new_translated_text`: New translated content
- `updated_at`: Timestamp of the change