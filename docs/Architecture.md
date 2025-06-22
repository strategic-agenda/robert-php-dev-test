# Explaining the architecture and design decisions
## System Structure for Multilingual Content

To efficiently handle multilingual content, the system should be modular, separating core translation logic from language-specific resources. A service-oriented architecture (SOA) or microservices approach can be used, with dedicated services for translation memory, terminology management, and user management. Content should be stored in a normalized format (e.g., XLIFF or TMX) to facilitate easy import/export and integration with other tools.

## Design Patterns

- **Repository Pattern:** Abstracts data access, making it easy to switch databases or storage backends.
- **Factory Pattern:** Creates language-specific processing components (tokenizers, parsers) as needed.
- **Observer Pattern:** Notifies components (e.g., UI, translation memory) of changes in translation units.
- **Strategy Pattern:** Allows dynamic selection of translation algorithms (e.g., rule-based, statistical, neural).
- **Singleton Pattern:** Ensures a single instance of shared resources like configuration or cache.

These patterns promote scalability (by decoupling components), maintainability (by isolating changes), and flexibility (by supporting plug-and-play modules).

## Proposed Database Schema

**Tables:**

- `projects` (id, name, source_language, target_language, created_at)
- `documents` (id, project_id, name, path, created_at)
- `translation_units` (id, document_id, source_text, target_text, status, version, created_at, updated_at)
- `users` (id, username, email, role)
- `translation_history` (id, translation_unit_id, user_id, old_target_text, new_target_text, changed_at)

This schema supports tracking translations at the segment level, associating them with documents and projects, and recording changes for version control.

## Version Control for Translations

Each translation unit includes a `version` field and is linked to a `translation_history` table. Every update to a translation unit creates a new history record, preserving previous versions. The folder structure can mirror the project and document hierarchy, with each document containing its translation units as files or database records. Integration with Git or a similar VCS can be used for file-based versioning, enabling rollback and collaborative editing.

# API Endpoints Summary

## Projects
- `GET /api/projects` - List all projects  
- `GET /api/projects/{id}` - Get a specific project  
- `POST /api/projects` - Create a new project  
- `PUT /api/projects/{id}` - Update a project  
- `DELETE /api/projects/{id}` - Delete a project and all its related data  

## Documents
- `GET /api/documents` - List all documents  
- `GET /api/documents?project_id={id}` - List documents for a specific project  
- `GET /api/documents/{id}` - Get a specific document  
- `POST /api/documents` - Create a new document  
- `PUT /api/documents/{id}` - Update a document  
- `DELETE /api/documents/{id}` - Delete a document and all its related data  

## Translation Units
- `GET /api/translation-units` - List all translation units  
- `GET /api/translation-units?document_id={id}` - List units for a specific document  
- `GET /api/translation-units/{id}` - Get a specific translation unit  
- `POST /api/translation-units` - Create a new translation unit  
- `PUT /api/translation-units/{id}` - Update a translation unit  
- `DELETE /api/translation-units/{id}` - Delete a translation unit and its history  

## Translation History
- `GET /api/translation-history/{translation_unit_id}` - Get history for a specific unit  