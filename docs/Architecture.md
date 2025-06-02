# Explaining the architecture and design decisions


1. Handling Multilingual Content Efficiently: 
In order to handle multilingual content effectively, I would implement the following:

* Language-aware Architecture: Use ISO language codes (e.g., en, fr, es) and store content in a language-specific table or column.

* Normalization: Break documents into translation units and store them separately with references to the original document.

* Caching: Use Redis or  for fast access to frequently requested translations.

* Indexing: Implement full-text indexing (e.g., MySQL FULLTEXT or ElasticSearch) to support fast searching across translation units.

2. Design Patterns for Scalability and Maintainability
In order to ensure scalability and maintainability for the application, I would implement the following design patterns:

* Repository Pattern: The repository pattern is used to abstract the data access layer and i have implemented it in the TranslationUnit class which handles the CRUD operations.

* Service Layer Pattern: The service layer pattern would also be be very useful for encapsulating business logic related to translation operations 

3. Database Schema can be found in the database/schema.sql

4. Version control implementation:

* Every update to a translation unit creates a new entry in translation_unit_versions.

* The latest version is reflected in the main translation_units table.

* Rollbacks can be performed by restoring an earlier version from the history.

# API DESIGN

| Method | Endpoint                  | Description                   |
| ------ | ------------------------- | ----------------------------- |
| GET    | `/api/units`              | List all translation units    |
| GET    | `/api/unit/{id}`          | Retrieve a specific unit      |
| POST   | `/api/unit`               | Create a new translation unit |
| PUT    | `/api/unit/{id}`          | Update a unit (new version)   |
| DELETE | `/api/unit/{id}`          | Delete a unit                 |
| POST   | `/api/create_document`    | Create a new document         |