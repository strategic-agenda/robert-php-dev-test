# Architecture and Design Decisions

The architecture of the PHP application, as represented by the `TranslationUnit` class and its interaction with the database through PDO, is designed to efficiently manage translation units for a multilingual content system. This document explains the key architectural and design decisions made in the development of this system.

## Overview

The `TranslationUnit` class is a central component of the application, encapsulating the logic for managing translation units, including CRUD (Create, Read, Update, Delete) operations. It interacts with a MySQL database using the PDO extension, which provides a consistent interface for accessing various database types.

## Key Components

### TranslationUnit Class

- **Responsibility**: Manages translation units.
- **Methods**:
  - `getAllTranslationUnits()`: Retrieves all translation units.
  - `addTranslationUnit(string $text, int $languageId)`: Adds a new translation unit.
  - `updateTranslationUnit(int $id, string $newText)`: Updates an existing translation unit.
  - `getTranslationUnit(int $id)`: Retrieves a specific translation unit.
  - `deleteTranslationUnit(int $id)`: Deletes a translation unit.

### PDO Database Interaction

- **Usage**: The PDO extension is used for database interaction, providing a database-agnostic interface and ensuring security against SQL injection through prepared statements.
- **Transactions**: Used in `updateTranslationUnit` and `deleteTranslationUnit` to ensure atomicity. In case of an error, changes are rolled back to maintain data integrity.

### Exception Handling

- Robust exception handling in transactional methods to handle any potential errors during database operations.

## Design Decisions

### Use of PDO

- **Reason**: PDO provides a secure, consistent interface for interacting with the database. Its use of prepared statements helps prevent SQL injection attacks.
- **Benefit**: Easy switching between different database types without changing the application code.

### Single Responsibility Principle

- The `TranslationUnit` class is designed to handle all operations related to translation units, adhering to the Single Responsibility Principle for better maintainability and scalability.

### Exception Handling and Transactions

- Ensures data integrity and provides feedback in case of operation failures, making the system more robust and reliable.

### Method Design

- CRUD operations are encapsulated in methods within the `TranslationUnit` class, making the code modular, easier to understand, and test.

## Version Control System for Translations

An essential aspect of the application's architecture is the version control system implemented for managing translations. This system is designed to track changes to translation units over time, allowing for efficient management of revisions and history.

### Key Features

- **Translation Versions Table**: A separate database table `translation_versions` is used to store historical versions of each translation unit. This table includes fields for `translation_unit_id`, `translated_text`, `version`, and `created_at`.

- **Tracking Changes**: Whenever a translation unit is updated, a new record is added to the `translation_versions` table. This record includes the current text of the translation, a version number, and a timestamp.

- **Version Numbering**: Each new version of a translation is assigned a unique version number, which is incremented with each update. This facilitates easy identification and retrieval of specific versions.

- **Retrieval of Historical Data**: The system allows for querying past versions of a translation, enabling users to view the history of changes and revert to previous versions if necessary.

### Implementation in `TranslationUnit` Class

- **`updateTranslationUnit` Method**: This method is responsible for updating translation units. When an update occurs, it not only modifies the `translation_units` table but also adds a new entry to the `translation_versions` table to record the change.

- **Transactional Integrity**: Updates to both the `translation_units` and `translation_versions` tables are performed within a database transaction to ensure data integrity. If an error occurs during the update, the transaction is rolled back, and no changes are made.

### Benefits

- **Historical Tracking**: Enables tracking the evolution of each translation unit, which is crucial in environments where accuracy and history of changes are important.
- **Audit Trail**: Provides an audit trail for changes, which can be useful for reviewing and understanding the modifications made over time.
- **Reversion Capability**: Facilitates the ability to revert to previous versions in case of erroneous updates or for historical reference.

### Future Enhancements

- **User Tracking**: Enhance the system to track which user made each change. This can be done by adding a `user_id` field to the `translation_versions` table and linking it to a users table.
- **Diff Viewing**: Implement functionality to view the differences between versions, making it easier to see what was changed in each update.
- **Automated Cleanup**: Establish policies for the retention of historical data, such as automatically purging very old versions or archiving them elsewhere.

## Future Considerations

- **Scalability**: The current design is suitable for small to medium-sized applications. For larger applications, consider implementing a more scalable architecture like microservices.
- **Caching**: Implement caching mechanisms for frequently accessed data to improve performance.
- **API Layer**: For a web-based application, an API layer (e.g., REST API) can be introduced to interact with the `TranslationUnit` class.
- **Unit Testing**: Enhance unit tests to cover more edge cases and scenarios for robustness.

## Conclusion

The architecture of the PHP application is designed with simplicity, maintainability, and security in mind. By using the PDO extension for database interaction and adhering to solid design principles, the application ensures efficient management of translation units while providing a robust and flexible platform for future enhancements.
