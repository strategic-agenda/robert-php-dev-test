# Computer-Assisted Translation (CAT) Tool Architecture

## System Architecture

The CAT tool is built using a layered architecture with the following components:

1. **Frontend Layer**: A React-based UI that allows users to interact with translation units
2. **API Layer**: RESTful API endpoints for managing translation units
3. **Business Logic Layer**: PHP classes implementing the core functionality using the Service pattern
4. **Data Access Layer**: Repository classes for database interaction
5. **Database Layer**: MySQL database storing translation units and their history

### Architecture Diagram

```
┌─────────────────┐        ┌────────────────┐        ┌─────────────────┐        ┌────────────────┐
│                 │        │                │        │                 │        │                │
│  React Frontend │◄─────► │  RESTful API   │◄─────► │    Services     │◄─────► │  Repositories  │◄─────► Database
│                 │        │                │        │                 │        │                │
└─────────────────┘        └────────────────┘        └─────────────────┘        └────────────────┘
```

## Design Patterns

### 1. Dependency Injection

The application uses dependency injection to provide dependencies to classes, improving testability and flexibility.

```php
class TranslationUnitService {
    private $repository;
    
    public function __construct(TranslationUnitRepositoryInterface $repository) {
        $this->repository = $repository;
    }
}
```

### 2. Repository Pattern

Used to abstract the data access layer, completely separating business logic from data access.

```php
class TranslationUnitRepository implements TranslationUnitRepositoryInterface {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getById($id) { /* ... */ }
    public function getAll() { /* ... */ }
    public function create(array $data) { /* ... */ }
    public function update($id, array $data) { /* ... */ }
    public function delete($id) { /* ... */ }
}
```

### 3. Service Pattern

Services encapsulate business logic and orchestrate operations, using repositories for data access.

```php
class TranslationUnitService implements TranslationUnitServiceInterface {
    private $repository;
    
    public function __construct(TranslationUnitRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    public function getAll() { /* ... */ }
    public function get($id) { /* ... */ }
    public function create(array $data) { /* ... */ }
    public function updateTranslation($id, $targetText) { /* ... */ }
}
```

### 4. Interface Segregation

The application uses interfaces to define contracts between components, allowing for flexible implementation and easier testing.

```php
interface TranslationUnitRepositoryInterface {
    public function getById($id);
    public function getAll();
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
```

### 5. Observer Pattern

Implemented in the history tracking system, where changes to a translation unit are automatically observed and recorded in the history table.

## Multilingual Content Handling

The system handles multilingual content through:

1. **Language Code Storage**: Each translation unit stores source and target language codes (e.g., 'en', 'fr')
2. **Character Set Support**: UTF-8 encoding throughout the system ensures proper handling of international characters
3. **Text Segmentation**: The system can be extended to include smart text segmentation for different languages
4. **Centralized Language Management**: A dedicated languages table stores metadata for all supported languages

## Database Schema

The database uses a relational model with the following tables:

### Languages Table
- `id`: Primary key
- `code`: Unique language code (e.g., 'en', 'fr')
- `name`: Language name (e.g., 'English', 'French')
- `direction`: Text direction (ltr or rtl)
- `active`: Whether the language is active
- `created_at`: Timestamp of creation

### Translation Units Table
- `id`: Primary key
- `source_text`: Original text to be translated
- `target_text`: Translated text
- `source_language`: Reference to language code
- `target_language`: Reference to language code
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
2. **Efficient Error Handling**: Structured error handling with appropriate HTTP status codes
3. **Environment-based Configuration**: Different configurations for development and production environments
4. **Horizontal Scaling**: The separated frontend and backend architecture allows for independent scaling

## API Design

The RESTful API follows standard HTTP methods with structured routing:

- `GET /api/languages`: Retrieve all languages
- `GET /api/languages/{id}`: Retrieve a specific language
- `POST /api/languages`: Create a new language
- `PUT /api/languages/{id}`: Update a language
- `DELETE /api/languages/{id}`: Delete a language

- `GET /api/units`: Retrieve all translation units
- `GET /api/units/{id}`: Retrieve a specific translation unit
- `GET /api/units/{id}/history`: Retrieve history for a translation unit
- `POST /api/units`: Create a new translation unit
- `PUT /api/units/{id}`: Update an existing translation unit
- `DELETE /api/units/{id}`: Delete a translation unit

## Testing Infrastructure

The application includes a comprehensive testing infrastructure:

1. **Unit Tests**: Test individual components in isolation using mock dependencies
2. **Integration Tests**: Test components working together with a real database
3. **Test Database**: A separate database for testing to avoid affecting production data
4. **PHPUnit Configuration**: Configured to run different test suites (Unit and Integration)

## Future Extensibility

The architecture is designed to be extensible for future features:

1. **Authentication System**: Can be added to secure API endpoints
2. **Machine Translation Integration**: Can be integrated as a service
3. **Translation Memory**: Can be implemented for reusing previous translations
4. **Terminology Management**: Can be added for consistent terminology across translations
