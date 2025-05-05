# Explaining the architecture and design decisions

# Robert CAT Tool - Simplified Architecture

This document outlines the architectural design for the Robert Computer-Assisted Translation (CAT) tool, focusing on the core features implemented in the current version.

## 1. System Overview

The Robert CAT tool is designed as a web-based application that facilitates the translation of documents by breaking them down into manageable translation units, allowing translators to work efficiently.

### Core Components

1. **Backend (PHP)**

   - TranslationUnit management
   - API endpoints for CRUD operations
   - Version control for translations

2. **Frontend (React)**
   - Translation unit listing
   - Translation editing interface
   - Status management

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

### Observer Pattern

The Observer pattern enables components to be notified when translation units change:

```php
// TranslationUnit implements SplSubject
class TranslationUnit implements SplSubject, JsonSerializable
{
    private array $observers = [];

    public function attach(SplObserver $observer): void {
        // Implementation...
    }

    public function detach(SplObserver $observer): void {
        // Implementation...
    }

    public function notify(): void {
        // Implementation...
    }

    // Other methods...
}
```

This pattern allows for:

- Real-time updates when translations change
- Loose coupling between the translation unit and components that use it
- Extensibility for adding new observers without modifying TranslationUnit code

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

The simplified database schema includes:

```sql
-- Languages table
CREATE TABLE languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    direction ENUM('ltr', 'rtl') DEFAULT 'ltr'
);

-- Documents table
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    source_language_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (source_language_id) REFERENCES languages(id)
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
    UNIQUE KEY (translation_unit_id, language_id)
);

-- Translation versions table for version control
CREATE TABLE translation_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    translation_id INT NOT NULL,
    version_number INT NOT NULL,
    content TEXT NOT NULL,
    modified_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (translation_id) REFERENCES translations(id) ON DELETE CASCADE,
    UNIQUE KEY (translation_id, version_number)
);
```

## 5. Version Control Implementation

Version control for translations is implemented through:

1. **History Tracking**: Each change to a translation is recorded in the translation unit's history array:

   ```php
   private function addToHistory(string $field, $oldValue, $newValue, array $metadata = []): void
   {
       $this->history[] = [
           'field' => $field,
           'old_value' => $oldValue,
           'new_value' => $newValue,
           'changed_at' => date('Y-m-d H:i:s'),
           'metadata' => $metadata
       ];
   }
   ```

2. **Version Storage**: Versions are saved to the `translation_versions` table with sequential version numbers.

3. **Restoration**: Previous versions can be restored:
   ```php
   public function restoreFromHistory(int $versionIndex): bool
   {
       // Implementation...
   }
   ```

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

### History

- `GET /api/translations/{id}/history` - Get version history for a unit

## 7. Frontend Components

The frontend consists of:

1. **TranslationList Component**: Displays a paginated list of translation units with their current translations, allowing users to select units for editing.

2. **TranslationForm Component**: Provides an interface for adding or editing translations, with fields for the translation content and status.

3. **Translation Service**: Handles API communication between the React components and the backend.

## 8. Testing

Unit tests cover:

1. **TranslationUnit Creation**: Verifies that units are created correctly with proper initial state.

2. **Translation Management**: Tests adding, updating, and retrieving translations for different languages.

3. **History Tracking**: Validates that changes to content and translations are properly recorded in history.

4. **Version Restoration**: Ensures that units can be restored to previous versions.

5. **Observer Notifications**: Tests that observers are properly notified when units change.

## 9. Security Considerations

Basic security measures include:

1. **Input Validation**: All user input is validated before processing.

2. **Prepared Statements**: SQL queries use prepared statements to prevent SQL injection.

3. **CORS Headers**: Appropriate CORS headers are set for API endpoints.

4. **Error Handling**: Errors are logged but not exposed to users in production.

## 10. Future Extensions

While not currently implemented, the system is designed to allow for future extensions such as:

1. **User Authentication**: Adding role-based access control for translators and reviewers.

2. **Translation Memory**: Implementing suggestion systems based on previous translations.

3. **Machine Translation Integration**: Adding support for machine translation services.

4. **Advanced Segmentation**: Implementing smarter text segmentation algorithms.

This architecture provides a solid foundation for the current implementation while allowing for future growth.
