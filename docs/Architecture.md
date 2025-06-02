### Robert PHP Developer Test - Architecture Overview

Over the course of building this Computer-Assisted Translation (CAT) tool, I focused on creating a modular, maintainable, and scalable system that allows users to manage translation units and their versions effectively.

#### System Structure and Multilingual Content Handling

The system is structured around clear separation of concerns. The backend is built with PHP, while the frontend uses ReactJS. I used a repository pattern to isolate database access logic, making the system cleaner and more testable. To manage multilingual content, each translation unit consists of a source text and optional translated texts linked to different target languages. These relationships are stored in relational database tables with proper foreign key constraints.

The system also incorporates version control by tracking changes to translations. Every time a translation is updated, the previous version is saved in a `translation_history` table along with a version number, allowing users to view and revert changes if necessary.

#### Design Patterns and Scalability

I applied the Repository Pattern to abstract data access from the application logic. This not only makes testing easier but also allows for future changes in the storage mechanism (e.g., switching from MySQL to another database) with minimal disruption.

The system is RESTful, with clean and well-defined endpoints, making it flexible for integration with different frontend frameworks or services. Each API route corresponds to a specific HTTP method and clearly defined behavior.

#### Database Schema Design

The relational database schema is designed to support flexibility and version tracking:

* **languages**: stores supported language IDs and codes (e.g., EN, ES, IT).
* **translation\_units**: stores source texts and their source language.
* **translations**: stores translated texts, linked to translation units and target languages, with versioning.
* **translation\_history**: stores previous versions of translations for audit and rollback capabilities.

#### Version Control Implementation

Version control is implemented at the database level. Each translation starts at version 1. When a translation is updated, the previous text and its version number are saved in the `translation_history` table. The active translation's version is incremented accordingly. This provides full visibility into the evolution of any translation.

#### PHP Class Implementation

The `TranslationUnitRepository` class includes methods to:

* Add a new translation unit.
* Retrieve a unit by ID.
* Update source or translated text.
* Save translation history automatically when changes occur.
* Delete a unit along with its translations and history.

These actions are exposed via a REST API that uses standard HTTP methods (`GET`, `POST`, `PUT`, `DELETE`).

#### React Frontend

The frontend, built in React, communicates with the API to display translation units, allow the addition of new units, editing of translations, and deletion. It also supports viewing translation history via a modal dialog. The UI is intuitive and responsive, using MUI components.

#### Testing

Although testing was new to me, I started experimenting with PHPUnit. I wrote unit tests for the repository class, checking:

* Insertion of translation units.
* Handling of empty text input.

It was a valuable learning experience, and I plan to deepen my knowledge of automated testing.

#### Final Thoughts

This project helped me understand and implement concepts like modular architecture, version control in data, and RESTful APIs. It was especially rewarding to build a complete flow from backend to frontend and see how each part connects. I’m excited to continue improving the application and expanding its features.
