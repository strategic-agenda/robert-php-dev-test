# Explaining the architecture and design decisions

# System Architecture

## Frontend

- Built in Next.js (App Router) with React, leveraging server components and client components where appropriate
- Uses i18n routing via next-intl for UI localization

## Backend

- PHP (Laravel or Symfony) - handles translation logic, document storage, segmentation, and user authentication
- Exposes REST API

## Database Layer

### PostgreSQL/MySQL

- Primary data storage

### ElasticSearch

- Fast lookup of previously translated segments
- Translation memory
- Fuzzy search

## Design Patterns

### Core Patterns

- **Factory Pattern**: Instantiates document parsers based on file type (e.g., .docx, .xliff, .xml, .txt)
- **Strategy Pattern**: Applies different segmentation strategies (e.g., sentence, phrase, paragraph) based on
  language/preferences
- **Observer Pattern**: Handles real-time updates in UI, ElasticSearch updates, and background tasks
- **Decorator Pattern**: Adds behavior (audit, logging) around translation updates
- **Adapter Pattern**: Integrates third-party tools/APIs

### Additional Patterns

- **Dependency Injection**: Enables flexibility and testability
- **Component-Driven Development**: Uses atomic design with reusable components

## Translations Version Control Implementation 

1. Previous versions are stored in a separate database table (see `translation_versions`)
2. When the current translation is updated:
    - Previous translation for a unit/language combination is saved
    - New row created with the version = MAX(version) + 1