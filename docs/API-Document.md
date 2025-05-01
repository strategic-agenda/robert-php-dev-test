# RESTful API Design for Computer-Assisted Translation (CAT) Tool

This document outlines the design of a RESTful API for the Computer-Assisted Translation (CAT) tool, enabling CRUD operations on translation units. The API follows RESTful principles, uses standard HTTP methods, and provides clear endpoints for managing translation units.

## Base URL
All endpoints are prefixed with the base URL:
```
/api/v1
```

## Authentication
- All endpoints require authentication via an API token or JWT (JSON Web Token) passed in the `Authorization` header.
- Example: `Authorization: Bearer <token>`

## Response Format
- All responses are in JSON format.
- Successful responses include a `data` field with the requested resource(s).
- Error responses include an `error` field with a message and appropriate HTTP status code.

### Success Response Example
```json
{
  "data": {
    "id": "unit_001",
    "source_text": "Hello, world!",
    "source_language": "en"
  }
}
```

### Error Response Example
```json
{
  "error": "Translation unit not found",
  "status": 404
}
```

## Endpoints

### 1. List All Translation Units
- **Endpoint**: `GET /api/v1/translation-units`
- **Description**: Retrieves a paginated list of all translation units.
- **Query Parameters**:
  - `page` (optional): Page number for pagination (default: 1).
  - `per_page` (optional): Number of items per page (default: 10).
  - `source_language` (optional): Filter by source language (e.g., `en`).
  - `target_language` (optional): Filter by target language of translations (e.g., `es`).
- **Response**:
  - **Status**: 200 OK
  - **Body**:
    ```json
    {
      "data": [
        {
          "id": "unit_001",
          "source_text": "Hello, world!",
          "source_language": "en",
          "translations": [
            {
              "target_language": "es",
              "target_text": "¡Hola, mundo!",
              "translator_id": "translator_001",
              "created_at": "2025-05-01T10:00:00Z",
              "updated_at": "2025-05-01T10:00:00Z"
            }
          ]
        },
        ...
      ],
      "meta": {
        "current_page": 1,
        "per_page": 10,
        "total": 50
      }
    }
    ```

### 2. Retrieve a Specific Translation Unit
- **Endpoint**: `GET /api/v1/translation-units/{id}`
- **Description**: Retrieves details of a specific translation unit by its ID.
- **Path Parameters**:
  - `id`: The unique identifier of the translation unit (e.g., `unit_001`).
- **Response**:
  - **Status**: 200 OK
  - **Body**:
    ```json
    {
      "data": {
        "id": "unit_001",
        "source_text": "Hello, world!",
        "source_language": "en",
        "translations": [
          {
            "target_language": "es",
            "target_text": "¡Hola, mundo!",
            "translator_id": "translator_001",
            "created_at": "2025-05-01T10:00:00Z",
            "updated_at": "2025-05-01T10:00:00Z"
          }
        ],
        "history": [
          {
            "action": "add",
            "target_language": "es",
            "text": "¡Hola, mundo!",
            "translator_id": "translator_001",
            "timestamp": "2025-05-01T10:00:00Z"
          }
        ]
      }
    }
    ```
  - **Error**:
    - **Status**: 404 Not Found
    - **Body**: `{ "error": "Translation unit not found" }`

### 3. Create a Translation Unit
- **Endpoint**: `POST /api/v1/translation-units`
- **Description**: Creates a new translation unit.
- **Request Body**:
  ```json
  {
    "source_text": "Hello, world!",
    "source_language": "en",
    "project_id": "project_001"
  }
  ```
- **Response**:
  - **Status**: 201 Created
  - **Body**:
    ```json
    {
      "data": {
        "id": "unit_001",
        "source_text": "Hello, world!",
        "source_language": "en",
        "project_id": "project_001",
        "created_at": "2025-05-01T10:00:00Z",
        "updated_at": "2025-05-01T10:00:00Z"
      }
    }
    ```
  - **Error**:
    - **Status**: 422 Unprocessable Entity
    - **Body**: `{ "error": "Invalid source language" }`

### 4. Add a Translation to a Translation Unit
- **Endpoint**: `POST /api/v1/translation-units/{id}/translations`
- **Description**: Adds a translation to an existing translation unit.
- **Path Parameters**:
  - `id`: The unique identifier of the translation unit.
- **Request Body**:
  ```json
  {
    "target_language": "es",
    "target_text": "¡Hola, mundo!",
    "translator_id": "translator_001"
  }
  ```
- **Response**:
  - **Status**: 201 Created
  - **Body**:
    ```json
    {
      "data": {
        "translation_unit_id": "unit_001",
        "target_language": "es",
        "target_text": "¡Hola, mundo!",
        "translator_id": "translator_001",
        "created_at": "2025-05-01T10:00:00Z",
        "updated_at": "2025-05-01T10:00:00Z"
      }
    }
    ```
  - **Error**:
    - **Status**: 404 Not Found
    - **Body**: `{ "error": "Translation unit not found" }`
    - **Status**: 422 Unprocessable Entity
    - **Body**: `{ "error": "Translation for this language already exists" }`

### 5. Update a Translation
- **Endpoint**: `PUT /api/v1/translation-units/{id}/translations/{target_language}`
- **Description**: Updates an existing translation for a specific language in a translation unit.
- **Path Parameters**:
  - `id`: The unique identifier of the translation unit.
  - `target_language`: The language code of the translation to update (e.g., `es`).
- **Request Body**:
  ```json
  {
    "target_text": "¡Hola, mundo nuevo!",
    "translator_id": "translator_002",
    "reason": "Improved translation accuracy"
  }
  ```
- **Response**:
  - **Status**: 200 OK
  - **Body**:
    ```json
    {
      "data": {
        "translation_unit_id": "unit_001",
        "target_language": "es",
        "target_text": "¡Hola, mundo nuevo!",
        "translator_id": "translator_002",
        "created_at": "2025-05-01T10:00:00Z",
        "updated_at": "2025-05-01T10:10:00Z"
      }
    }
    ```
  - **Error**:
    - **Status**: 404 Not Found
    - **Body**: `{ "error": "Translation not found" }`
    - **Status**: 422 Unprocessable Entity
    - **Body**: `{ "error": "Invalid translator ID" }`

### 6. Delete a Translation Unit
- **Endpoint**: `DELETE /api/v1/translation-units/{id}`
- **Description**: Deletes a translation unit and its associated translations (soft delete).
- **Path Parameters**:
  - `id`: The unique identifier of the translation unit.
- **Response**:
  - **Status**: 204 No Content
  - **Body**: (Empty)
  - **Error**:
    - **Status**: 404 Not Found
    - **Body**: `{ "error": "Translation unit not found" }`

## Additional Notes
- **Versioning**: The API uses `/v1` to support future iterations without breaking existing clients.
- **Rate Limiting**: Implement rate limiting to prevent abuse, configurable per user or IP.
- **Validation**: Input validation is enforced (e.g., valid ISO 639-1 language codes, non-empty source text).
- **Security**: Use HTTPS, sanitize inputs to prevent SQL injection, and validate tokens to ensure authorized access.
- **Pagination**: Applied to the list endpoint to manage large datasets efficiently.
- **Soft Deletes**: Deletion operations mark records as deleted (via `deleted_at` timestamp) to preserve history.