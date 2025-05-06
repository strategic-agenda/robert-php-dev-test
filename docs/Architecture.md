# Robert CAT Tool - Architecture

This document outlines the architectural design for the Robert Computer-Assisted Translation (CAT) tool, focusing on the core features implemented in the current version.

## 1. System Overview

The Robert CAT tool is designed as a web-based application that facilitates the translation of documents by breaking them down into manageable translation units, allowing translators to work efficiently.

### Core Components

1. **Backend (PHP)**

   - TranslationUnit management
   - API endpoints for CRUD operations
   - Repository and Factory patterns

2. **Frontend (React with TypeScript)**
   - Translation unit listing and editing interface
   - Status management
   - Bootstrap-based UI components

## 2. Design Patterns Implemented

### Repository Pattern

The Repository pattern abstracts the data access layer, providing a clean API for translation unit management:

```php
// TranslationUnitRepositoryInterface defines the contract
interface TranslationUnitRepositoryInterface
{
    public function findById(int $id): ?TranslationUnit;
    public function findByDocumentId(int $documentId): array;
    public function save(TranslationUnit $unit): void;
    public function delete(int $id): bool;
}

// TranslationUnitRepository implements database operations
class TranslationUnitRepository implements TranslationUnitRepositoryInterface
{
    private PDO $pdo;

    // Implementation of methods...
}
```

This pattern allows for:

- Separation of business logic from data access code
- Easier unit testing through repository mocking
- Potential to switch database implementations without changing business logic

### Factory Pattern

The Factory pattern encapsulates the creation of TranslationUnit objects:

```php
class TranslationUnitFactory
{
    public function createFromText(
        int $documentId,
        int $sequenceNumber,
        string $sourceText,
        ?string $context = null
    ): TranslationUnit {
        // Implementation...
    }

    public function createFromArray(array $data): TranslationUnit {
        // Implementation...
    }

    // Other creation methods...
}
```

This pattern provides:

- Consistent object creation logic
- Ability to extend creation methods without modifying client code
- Centralized control over object initialization

## 3. Data Model

The core data model consists of:

### TranslationUnit

Represents a segment of text to be translated:

- **id**: Unique identifier
- **documentId**: ID of the parent document
- **sequenceNumber**: Position in the document
- **sourceContent**: Original text to be translated
- **context**: Optional context for the translator
- **translations**: Collection of translations in different languages

### Translation

Represents a translation of a unit into a specific language:

- **languageId**: Target language identifier
- **content**: Translated text
- **translatedBy**: User ID of the translator
- **status**: Current status (draft, reviewed, approved, rejected)
- **reviewedBy**: User ID of the reviewer (if applicable)
- **createdAt/updatedAt**: Timestamps

## 4. Database Schema

The database schema includes:

```sql
-- Languages table
CREATE TABLE languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Documents table
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    source_language_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (source_language_id) REFERENCES languages(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Translation units table
CREATE TABLE translation_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    sequence_number INT NOT NULL,
    source_content TEXT NOT NULL,
    context TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    UNIQUE KEY (document_id, sequence_number)
);

-- Translations table
CREATE TABLE translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_unit_id INT NOT NULL,
    language_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'reviewed', 'approved', 'rejected') DEFAULT 'draft',
    translated_by INT,
    reviewed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_unit_id) REFERENCES translation_units(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id),
    FOREIGN KEY (translated_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    UNIQUE KEY (translation_unit_id, language_id)
);
```

## 5. Proposed Version Control Implementation

While not currently implemented, here is a proposed approach for implementing version control for translations:

### Database Schema Addition

```sql
-- Translation versions table for version control
CREATE TABLE translation_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_id INT NOT NULL,
    content TEXT NOT NULL,
    status ENUM('draft', 'reviewed', 'approved', 'rejected'),
    translated_by INT,
    reviewed_by INT,
    version_number INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_id) REFERENCES translations(id) ON DELETE CASCADE,
    FOREIGN KEY (translated_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id),
    UNIQUE KEY (translation_id, version_number)
);
```

### TranslationUnit Class Extension

The TranslationUnit class would be extended to track versions:

```php
class TranslationUnit implements JsonSerializable
{
    // Existing properties...

    /**
     * Add a translation for a specific language and store the previous version
     *
     * @param int $languageId The language ID
     * @param string $content The translated content
     * @param int $translatedBy User ID of the translator
     * @return self
     */
    public function addTranslation(int $languageId, string $content, int $translatedBy): self
    {
        // Store previous version if exists
        $existingTranslation = $this->getTranslation($languageId);
        if ($existingTranslation) {
            $this->storeTranslationVersion($languageId, $existingTranslation);
        }

        // Add new translation
        $this->translations[$languageId] = [
            'content' => $content,
            'translated_by' => $translatedBy,
            'status' => 'draft',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this;
    }

    /**
     * Store a version of a translation
     *
     * @param int $languageId
     * @param array $translation
     * @return void
     */
    private function storeTranslationVersion(int $languageId, array $translation): void
    {
        // This would be implemented in the repository
    }

    /**
     * Get version history for a translation
     *
     * @param int $languageId
     * @return array
     */
    public function getTranslationVersions(int $languageId): array
    {
        // This would be implemented in the repository
        return [];
    }

    /**
     * Restore a translation to a previous version
     *
     * @param int $languageId
     * @param int $versionId
     * @return bool
     */
    public function restoreTranslationVersion(int $languageId, int $versionId): bool
    {
        // This would be implemented in the repository
        return true;
    }
}
```

### Repository Extension

The TranslationUnitRepository would be extended with version control methods:

```php
class TranslationUnitRepository implements TranslationUnitRepositoryInterface
{
    // Existing methods...

    /**
     * Save a translation version
     *
     * @param int $translationId
     * @param array $translationData
     * @return void
     */
    private function saveTranslationVersion(int $translationId, array $translationData): void
    {
        // Get the next version number
        $stmt = $this->pdo->prepare(
            "SELECT MAX(version_number) FROM translation_versions WHERE translation_id = :translation_id"
        );
        $stmt->execute(['translation_id' => $translationId]);
        $maxVersion = $stmt->fetchColumn();
        $nextVersion = $maxVersion ? $maxVersion + 1 : 1;

        // Insert the version
        $stmt = $this->pdo->prepare(
            "INSERT INTO translation_versions
             (translation_id, content, status, translated_by, reviewed_by, version_number)
             VALUES (:translation_id, :content, :status, :translated_by, :reviewed_by, :version_number)"
        );

        $stmt->execute([
            'translation_id' => $translationId,
            'content' => $translationData['content'],
            'status' => $translationData['status'],
            'translated_by' => $translationData['translated_by'],
            'reviewed_by' => $translationData['reviewed_by'] ?? null,
            'version_number' => $nextVersion
        ]);
    }

    /**
     * Get version history for a translation
     *
     * @param int $translationId
     * @return array
     */
    public function getTranslationVersions(int $translationId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM translation_versions
             WHERE translation_id = :translation_id
             ORDER BY version_number DESC"
        );
        $stmt->execute(['translation_id' => $translationId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Restore a translation to a previous version
     *
     * @param int $translationId
     * @param int $versionId
     * @return bool
     */
    public function restoreTranslationVersion(int $translationId, int $versionId): bool
    {
        // First, get the version data
        $stmt = $this->pdo->prepare(
            "SELECT * FROM translation_versions WHERE id = :id AND translation_id = :translation_id"
        );
        $stmt->execute([
            'id' => $versionId,
            'translation_id' => $translationId
        ]);

        $versionData = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$versionData) {
            return false;
        }

        // Update the translation with the version data
        $stmt = $this->pdo->prepare(
            "UPDATE translations
             SET content = :content,
                 status = :status,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );

        $stmt->execute([
            'id' => $translationId,
            'content' => $versionData['content'],
            'status' => $versionData['status']
        ]);

        return $stmt->rowCount() > 0;
    }
}
```

### Frontend Implementation

The frontend would be extended with components for version history:

1. **TranslationVersionHistory Component**: A new component that displays the version history for a translation unit, showing:

   - Version number
   - Date/time of creation
   - User who made the change
   - Status at that point in time
   - Content snapshot

2. **TranslationVersionCompare Component**: A component to compare two versions side-by-side with:

   - Highlighting of changes
   - Diff visualization
   - Option to restore to a previous version

3. **API Endpoints**: New endpoints would be added:
   - `GET /api/translations/{id}/versions` - Get version history
   - `GET /api/translations/{id}/versions/{versionId}` - Get a specific version
   - `POST /api/translations/{id}/restore/{versionId}` - Restore to a specific version

This implementation would provide a complete version history, allowing translators and reviewers to track changes, compare versions, and restore previous translations if needed.

## 6. API Design

The RESTful API provides the following endpoints:

### Translation Units

- `GET /api/translations` - List all units (with pagination and filtering)
- `GET /api/translations/{id}` - Get a specific unit
- `POST /api/translations` - Create a new unit
- `PUT /api/translations/{id}` - Update a unit
- `DELETE /api/translations/{id}` - Delete a unit

### Translations

- `POST /api/translations/{id}/translate` - Add a translation for a unit
- `PUT /api/translations/{id}/status` - Update translation status

## 7. Frontend Components

The frontend consists of:

1. **TranslationList Component**: Displays a paginated list of translation units with their current translations, allowing users to select units for editing.

2. **TranslationForm Component**: Provides an interface for adding or editing translations, with fields for the translation content and status.

3. **TranslationActions Component**: Provides actions for managing translation units, such as adding new units.

4. **Translation Service**: Handles API communication between the React components and the backend.

## 8. Testing

Unit tests cover:

1. **TranslationUnit Creation**: Verifies that units are created correctly with proper initial state.

2. **Translation Management**: Tests adding, updating, and retrieving translations for different languages.

3. **JSON Serialization**: Tests that units are properly serialized to JSON.

4. **Repository Operations**: Tests that the repository correctly handles database operations.

## 9. Security Considerations

Basic security measures include:

1. **Input Validation**: All user input is validated before processing.

2. **Prepared Statements**: SQL queries use prepared statements to prevent SQL injection.

3. **CORS Headers**: Appropriate CORS headers are set for API endpoints.

4. **Error Handling**: Errors are logged but not exposed to users in production.

## 10. Future Extensions

The system is designed to allow for future extensions such as:

1. **User Authentication**: Adding role-based access control for translators and reviewers.

2. **Translation Memory**: Implementing suggestion systems based on previous translations.

3. **Machine Translation Integration**: Adding support for machine translation services.

4. **Advanced Segmentation**: Implementing smarter text segmentation algorithms.

5. **Version Control**: Implementing the version control system described above.

This architecture provides a solid foundation for the current implementation while allowing for future growth.
