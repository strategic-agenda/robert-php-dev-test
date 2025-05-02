# Robert CAT Tool - Architecture Documentation

## 1. System Overview

Robert CAT Tool is a Computer-Assisted Translation system designed to manage and streamline the translation process for content across multiple languages. The application follows a RESTful API architecture with clear separation of concerns, implementing a custom MVC-like pattern.

## 2. Directory Structure

```
robert-php-dev-test/
├── api/                  # API endpoints and controllers
│   ├── api_router.php    # Routes API requests to controllers
│   ├── BaseApiController.php # Abstract base controller with common functionality
│   ├── LanguagesController.php # Language-specific operations
│   ├── TranslationsController.php # Translation operations
│   └── utils.php         # Utility functions for API
├── config/               # Configuration files
│   └── database.php      # Database configuration
├── database/             # Database-related files
│   └── schema.sql        # Database schema definition
├── docs/                 # Documentation
│   └── Architecture.md   # This file
├── frontend/             # Frontend code (React.js application)
├── src/                  # Core application logic
│   └── TranslationUnit.php # Business logic for translation units
├── tests/                # Test files
│   └── TranslationUnitTest.php # Tests for TranslationUnit class
├── bootstrap.php         # Application bootstrapping
└── vendor/               # Third-party dependencies
```

## 3. Architectural Patterns

### 3.1 Core Architecture

The application implements a custom architecture with elements of these patterns:

- **RESTful API**: Resources exposed through standardized HTTP methods
- **MVC-like Pattern**:
   - Controllers: Handle request/response cycle (LanguagesController, TranslationsController)
   - Models: Business logic and data access (TranslationUnit class)
   - Views: JSON responses (no traditional views, as this is an API)
- **Controller Hierarchy**: Uses inheritance to share common controller functionality

### 3.2 Design Patterns Used

1. **Template Method Pattern**: BaseApiController defines the algorithm skeleton for handling requests, with concrete implementations in child controllers
2. **Strategy Pattern**: Different handling strategies based on HTTP methods (GET, POST, PUT, DELETE)
3. **Repository Pattern**: TranslationUnit class encapsulates data access operations
4. **Factory Method**: Database connections created via factory methods

## 4. Key Components

### 4.1 API Layer

The API follows a RESTful architecture organized into controllers:

- **api_router.php**: Simple but effective routing mechanism that:
   - Uses regex patterns to match request paths
   - Instantiates the appropriate controller based on the route
   - Provides basic error handling for unknown endpoints

- **BaseApiController**: Abstract class providing common functionality for all API controllers:
   - Defines the common API handling workflow
   - Request handling and routing to appropriate HTTP methods (GET, POST, PUT, DELETE)
   - Database connection management
   - Common utility methods (pagination, parameter binding, search and filtering)
   - Error handling
   - Forces child controllers to implement specific HTTP method handlers

### 4.2 Specialized Controllers

- **LanguagesController**:
   - Manages language-related operations (CRUD for available languages)
   - Implements validation rules for language creation/updates
   - Handles soft deletion when languages are in use

- **TranslationsController**:
   - More complex controller that manages two related resources:
      - Translation units (original content)
      - Translations (localized versions)
   - Implements custom logic for POST/PUT based on the request body
   - Handles resource relationships

### 4.3 Core Business Logic

The `TranslationUnit` class in the `src` directory implements core business logic for:
- Adding and updating translation units
- Adding and updating translations
- Maintaining version history for translation changes
- Uses database transactions for data integrity
- Manages version history when content changes
- Provides a cleaner separation between API and business layers

### 4.4 Database Schema

The application uses a relational database with the following key tables:

1. **users**
   - Stores user accounts with role-based permissions
   - Roles: admin, translator, reviewer, project_manager

2. **languages**
   - Stores available languages with properties like code, name, and RTL support
   - Properties: code, name, RTL support, enabled status

3. **translation_units**
   - Original content that needs translation
   - Includes context information for translators
   - Tracks creation/modification

4. **translations**
   - Translated content for each unit/language combination
   - Tracks revision history
   - Maintains approval workflow states

5. **translation_unit_history** and **translation_history**
   - Track all changes to content over time
   - Enable version comparison and auditing

## 5. API Endpoints

The application provides several RESTful API endpoints:

### 5.1 Languages API
- `GET /api/languages`: List all languages with pagination and filtering
- `GET /api/languages/{id}`: Get details for a specific language
- `POST /api/languages`: Create a new language
- `PUT /api/languages/{id}`: Update an existing language
- `DELETE /api/languages/{id}`: Delete or disable a language

### 5.2 Translations API
- `GET /api/translations`: List all translation units with pagination and filtering
- `GET /api/translations/{id}`: Get a specific translation unit with its translations
- `POST /api/translations`: Create a new translation unit or add a translation
- `PUT /api/translations/{id}`: Update a translation unit or a translation
- `DELETE /api/translations/{id}`: Archive a translation unit

## 6. Request Flow

1. HTTP request arrives at the server
2. `api_router.php` parses the URL and routes to the appropriate controller
3. Controller constructor initializes (BaseApiController):
   - Sets up CORS headers
   - Establishes database connection
   - Extracts resource ID from URL (if applicable)
   - Parses request body (for POST/PUT)
   - Calls handleRequest() method
4. handleRequest() routes to the appropriate method based on HTTP method
5. Controller executes business logic and prepares response
6. JSON response is sent back to the client

## 7. Technical Implementation Details

### 7.1 Security Considerations

- All database queries use prepared statements to prevent SQL injection
- Proper input validation in controllers
- CORS headers management
- Error messages sanitized in production mode

### 7.2 API Response Format

- Consistent JSON response structure
- Proper HTTP status codes
- Pagination information included for list endpoints
- Error responses with descriptive messages

### 7.3 Pagination and Filtering

- Handles offset/limit parameters
- Returns metadata about total counts and pages
- Default page sizes configurable per resource
- Dynamic WHERE clause construction based on search parameters
- Support for multiple search fields with OR/AND conditions
- Parameter binding for secure queries

## 8. Workflow Examples

### 8.1 Adding a New Language

1. Client sends POST to `/api/languages` with language details
2. LanguagesController validates input
3. Checks for conflicts (e.g., duplicate language code)
4. Inserts new record into the database
5. Returns success response with new ID

### 8.2 Translation Workflow

1. Create translation unit via POST to `/api/translations`
2. For each target language:
   - Add translation via POST with unit ID and language ID
3. Retrieve translation unit with all translations via GET
4. Update translations as needed via PUT
5. Translations maintain revision history automatically

## 9. Key Features

- **Multilingual Support**: Handles multiple languages including RTL languages
- **Version History**: Tracks changes to both translation units and translations
- **User Roles**: Supports different user types (admin, translator, reviewer, project manager)
- **Soft Delete**: Archives translation units instead of permanently deleting them
- **Search & Filtering**: Provides search functionality across translation units
- **Pagination**: Implements pagination for list endpoints

## 10. Database Relationships

- A **Language** can have many **Translations**
- A **TranslationUnit** can have many **Translations** (one per language)
- A **User** can create/update **TranslationUnits** and **Translations**
- Changes to **TranslationUnits** are recorded in **TranslationUnitHistory**
- Changes to **Translations** are recorded in **TranslationHistory**

## 11. Strengths and Future Enhancements

### 11.1 Strengths

- **Clean Architecture**: Clear separation of concerns
- **Extensibility**: Easy to add new controllers or endpoints
- **History Tracking**: Built-in versioning for all content changes
- **Soft Delete**: Non-destructive operations where appropriate
- **Performance Considerations**: Proper indexing and pagination

### 11.2 Future Enhancement Considerations

- Implement a dedicated Router class for more flexible routing
- Add a dependency injection container for better testability
- Consider implementing a repository layer to further separate data access
- Add API documentation using OpenAPI/Swagger
- Implement more robust authentication and authorization middleware
- Consider caching frequently accessed data
