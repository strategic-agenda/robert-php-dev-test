# Explaining the architecture and design decisions

## Overview
This is a Computer-Assisted Translation (CAT) tool built with PHP and React. The application follows a client-server architecture with a RESTful API backend and a modern React frontend.

## Core Components

### Backend (PHP)
1. **Model Layer**
   - `TranslationUnit`: Core domain model representing a translation with history tracking
   - Implements immutable history tracking for all translation changes

2. **Repository Layer**
   - `TranslationUnitRepository`: Handles database operations using Doctrine DBAL
   - Implements data mapping between camelCase (PHP) and snake_case (Database)

3. **Controller Layer**
   - `TranslationUnitController`: RESTful API endpoints for CRUD operations
   - Implements input validation and error handling

### Database
- MySQL database with two main tables:
  - `translation_units`: Stores current translation state
  - `translation_history`: Maintains translation version history
- Uses appropriate indexes for efficient querying
- UTF-8MB4 encoding for full Unicode support

### Frontend (React)
- Modern React application with component-based architecture
- Communicates with backend via RESTful API
- Implements real-time translation interface

## Key Design Decisions

1. **History Tracking**
   - Immutable history records for all translation changes
   - Reverse chronological order (newest first)
   - Each entry contains translation text and timestamp

2. **Database Design**
   - Separate history table for efficient querying
   - Foreign key constraints for data integrity
   - Indexes on frequently queried columns

3. **API Design**
   - RESTful principles for predictable endpoints
   - JSON for data exchange
   - Proper error handling and status codes

4. **Security**
   - Input validation at controller level
   - Prepared statements for database queries
   - CORS middleware for frontend access
