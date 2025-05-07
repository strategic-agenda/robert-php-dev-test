# Computer-Assisted Translation (CAT) Tool Architecture

## System Architecture

The CAT tool is built using a layered architecture with the following components:

1. **Frontend Layer**: A React-based UI that allows users to interact with translation units
2. **API Layer**: RESTful API endpoints for managing translation units
3. **Business Logic Layer**: PHP classes implementing the core functionality
4. **Data Access Layer**: Database interaction for persistent storage
5. **Database Layer**: MySQL database storing translation units and their history

### Architecture Diagram

```
┌─────────────────┐        ┌────────────────┐        ┌─────────────────┐        ┌────────────────┐
│                 │        │                │        │                 │        │                │
│  React Frontend │◄─────► │  RESTful API   │◄─────► │  Business Logic │◄─────► │  Data Storage  │
│                 │        │                │        │                 │        │                │
└─────────────────┘        └────────────────┘        └─────────────────┘        └────────────────┘
```

## Design Patterns

### 1. Singleton Pattern

Used for database connection management to ensure only a single connection instance exists throughout the application's lifecycle.

```php
class Database {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### 2. Repository Pattern

Used to abstract the data access layer, separating business logic from data access.

```php
class TranslationUnitRepository {
    public function findById($id) { /* ... */ }
    public function findAll() { /* ... */ }
    public function save(TranslationUnit $unit) { /* ... */ }
    public function update(TranslationUnit $unit) { /* ... */ }
    public function delete(TranslationUnit $unit) { /* ... */ }
}
```

### 3. Active Record Pattern

The `TranslationUnit` class implements the Active Record pattern, handling both data storage and business logic related to a translation unit.

```php
class TranslationUnit {
    // Properties and behavior for a translation unit
    public function save() { /* ... */ }
    public function update() { /* ... */ }
    public static function findById($id) { /* ... */ }
}
```

### 4. Observer Pattern

Implemented in the history tracking system, where changes to a translation unit are automatically observed and recorded.

## Multilingual Content Handling

The system handles multilingual content through:

1. **Language Code Storage**: Each translation unit stores source and target language codes (e.g., 'en', 'fr')
2. **Character Set Support**: UTF-8 encoding throughout the system ensures proper handling of international characters
3. **Text Segmentation**: The system can be extended to include smart text segmentation for different languages
4. **Centralized Language Management**: A dedicated languages table stores metadata for all supported languages

## Database Schema

The database uses a relational model with the following tables:

### Languages Table
- `code`: Primary key, language code (e.g., 'en', 'fr')
- `name`: Language name (e.g., 'English', 'French')
- `direction`: Text direction (ltr or rtl)
- `active`: Whether the language is active
- `created_at`: Timestamp of creation

### Translation Units Table
- `id`: Primary key
- `source_text`: Original text to be translated
- `target_text`: Translated text
- `source_language`: Foreign key to languages table
- `target_language`: Foreign key to languages table
- `created_at`: Timestamp of creation
- `updated_at`: Timestamp of last update

### Translation History Table
- `id`: Primary key
- `translation_unit_id`: Foreign key to translation_units table
- `target_text`: Previous version of the target text
- `updated_at`: Timestamp of the update

This schema supports:
1. Retrieving the current state of translations
2. Tracking the full history of changes
3. Maintaining language metadata
4. Ensuring data integrity through foreign key constraints

## Version Control for Translations

The version control system for translations is implemented through:

1. **History Tracking**: Every change to a translation is recorded in the translation_history table
2. **Audit Trail**: Each history entry includes the previous version and timestamp
3. **Restoration Capability**: The system can retrieve and restore any previous version of a translation

## Scalability and Performance

To ensure the system can handle large volumes of translation data:

1. **Database Indexing**: Key fields are indexed for faster queries
2. **Pagination**: API endpoints support pagination for large result sets
3. **Caching**: The system can be extended with caching for frequently accessed translations
4. **Horizontal Scaling**: The separated frontend and backend architecture allows for independent scaling

## API Design

The RESTful API follows standard HTTP methods:

- `GET /api/units`: Retrieve all translation units
- `GET /api/units/{id}`: Retrieve a specific translation unit
- `POST /api/units`: Create a new translation unit
- `PUT /api/units/{id}`: Update an existing translation unit
- `DELETE /api/units/{id}`: Delete a translation unit

## Future Extensibility

The architecture is designed to be extensible for future features:

1. **Authentication System**: Can be added to secure API endpoints
2. **Machine Translation Integration**: Can be integrated as a service
3. **Translation Memory**: Can be implemented for reusing previous translations
4. **Terminology Management**: Can be added for consistent terminology across translations
