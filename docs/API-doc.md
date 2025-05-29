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
    "source_language": "en",
    "target_language": "es",
    "target_text": "¡Hola, mundo!",
    "project_id": "project_001",
    "created_at": "2024-03-21T10:00:00Z",
    "updated_at": "2024-03-21T10:00:00Z",
    "deleted_at": null
  }
}
```

### Error Response Example

```json
{
  "error": "Translation unit not found"
}
```

## Endpoints

### 1. List All Translation Units

- **Endpoint**: `GET /api/v1/translation-units`
- **Description**: Retrieves a paginated list of all translation units.
- **Query Parameters**:
  - `page` (optional): Page number for pagination (default: 1)
  - `per_page` (optional): Number of items per page (default: 10)
  - `source_language` (optional): Filter by source language (e.g., `en`)
  - `target_language` (optional): Filter by target language (e.g., `es`)
  - `project_id` (optional): Filter by project ID
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
          "target_language": "es",
          "target_text": "¡Hola, mundo!",
          "project_id": "project_001",
          "created_at": "2024-03-21T10:00:00Z",
          "updated_at": "2024-03-21T10:00:00Z",
          "deleted_at": null,
          "history": [
            {
              "id": "history_001",
              "translation_unit_id": "unit_001",
              "previous_text": "",
              "new_text": "¡Hola, mundo!",
              "changed_by": "user_001",
              "change_reason": "Initial translation",
              "created_at": "2024-03-21T10:00:00Z"
            }
          ]
        }
      ],
      "meta": {
        "total": 50,
        "per_page": 10,
        "current_page": 1,
        "last_page": 5
      }
    }
    ```

### 2. Retrieve a Specific Translation Unit

- **Endpoint**: `GET /api/v1/translation-units/{id}`
- **Description**: Retrieves details of a specific translation unit by its ID.
- **Path Parameters**:
  - `id`: The unique identifier of the translation unit
- **Response**:
  - **Status**: 200 OK
  - **Body**:
    ```json
    {
      "data": {
        "id": "unit_001",
        "source_text": "Hello, world!",
        "source_language": "en",
        "target_language": "es",
        "target_text": "¡Hola, mundo!",
        "project_id": "project_001",
        "created_at": "2024-03-21T10:00:00Z",
        "updated_at": "2024-03-21T10:00:00Z",
        "deleted_at": null,
        "history": [
          {
            "id": "history_001",
            "translation_unit_id": "unit_001",
            "previous_text": "",
            "new_text": "¡Hola, mundo!",
            "changed_by": "user_001",
            "change_reason": "Initial translation",
            "created_at": "2024-03-21T10:00:00Z"
          }
        ]
      }
    }
    ```

### 3. Create a Translation Unit

- **Endpoint**: `POST /api/v1/translation-units`
- **Description**: Creates a new translation unit.
- **Request Body**:
  ```json
  {
    "source_text": "Hello, world!",
    "source_language": "en",
    "target_language": "es",
    "project_id": "project_001",
    "target_text": "¡Hola, mundo!"
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
        "target_language": "es",
        "target_text": "¡Hola, mundo!",
        "project_id": "project_001",
        "created_at": "2024-03-21T10:00:00Z",
        "updated_at": "2024-03-21T10:00:00Z",
        "deleted_at": null
      }
    }
    ```

### 4. Update a Translation Unit

- **Endpoint**: `PUT /api/v1/translation-units/{id}`
- **Description**: Updates an existing translation unit.
- **Path Parameters**:
  - `id`: The unique identifier of the translation unit
- **Request Body**:
  ```json
  {
    "target_text": "¡Hola, mundo nuevo!",
    "changed_by": "user_002",
    "change_reason": "Improved translation accuracy"
  }
  ```
- **Response**:
  - **Status**: 200 OK
  - **Body**:
    ```json
    {
      "data": {
        "id": "unit_001",
        "source_text": "Hello, world!",
        "source_language": "en",
        "target_language": "es",
        "target_text": "¡Hola, mundo nuevo!",
        "project_id": "project_001",
        "created_at": "2024-03-21T10:00:00Z",
        "updated_at": "2024-03-21T10:10:00Z",
        "deleted_at": null,
        "history": [
          {
            "id": "history_001",
            "translation_unit_id": "unit_001",
            "previous_text": "¡Hola, mundo!",
            "new_text": "¡Hola, mundo nuevo!",
            "changed_by": "user_002",
            "change_reason": "Improved translation accuracy",
            "created_at": "2024-03-21T10:10:00Z"
          }
        ]
      }
    }
    ```

### 5. Delete a Translation Unit

- **Endpoint**: `DELETE /api/v1/translation-units/{id}`
- **Description**: Soft deletes a translation unit.
- **Path Parameters**:
  - `id`: The unique identifier of the translation unit
- **Response**:
  - **Status**: 200 OK
  - **Body**:
    ```json
    {
      "message": "Translation unit deleted successfully"
    }
    ```

### 6. Get Translation History

- **Endpoint**: `GET /api/v1/translation-units/{id}/history`
- **Description**: Retrieves the translation history for a specific unit.
- **Path Parameters**:
  - `id`: The unique identifier of the translation unit
- **Response**:
  - **Status**: 200 OK
  - **Body**:
    ```json
    {
      "data": [
        {
          "id": "history_001",
          "translation_unit_id": "unit_001",
          "previous_text": "",
          "new_text": "¡Hola, mundo!",
          "changed_by": "user_001",
          "change_reason": "Initial translation",
          "created_at": "2024-03-21T10:00:00Z"
        },
        {
          "id": "history_002",
          "translation_unit_id": "unit_001",
          "previous_text": "¡Hola, mundo!",
          "new_text": "¡Hola, mundo nuevo!",
          "changed_by": "user_002",
          "change_reason": "Improved translation accuracy",
          "created_at": "2024-03-21T10:10:00Z"
        }
      ]
    }
    ```

## Error Handling

### Common Error Codes

- `400 Bad Request`: Invalid request parameters
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server-side error

### Validation Rules

#### Create Translation Unit

- `source_text`: Required, string
- `source_language`: Required, string, format: xx or xx-XX (e.g., en, en-US)
- `target_language`: Required, string, format: xx or xx-XX (e.g., en, en-US)
- `project_id`: Required, string
- `target_text`: Optional, string

#### Update Translation Unit

- `target_text`: Required, string
- `changed_by`: Required, string
- `change_reason`: Optional, string

## Implementation Details

The API is implemented using Laravel's best practices with the following structure:

- **Controllers**: Handle HTTP requests and responses
- **Services**: Contain business logic and validation
- **Repositories**: Handle data access and persistence
- **Resources**: Transform models into JSON responses

### Directory Structure

```
api/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── TranslationUnitController.php
│   ├── Requests/
│   │   └── TranslationUnitRequest.php
│   └── Resources/
│       ├── TranslationUnitResource.php
│       └── TranslationHistoryResource.php
├── Repositories/
│   ├── TranslationUnitRepository.php
│   └── TranslationUnitRepositoryInterface.php
├── Services/
│   └── TranslationUnitService.php
└── Providers/
    └── RepositoryServiceProvider.php
```

## Rate Limiting

- 100 requests per minute per API token
- Rate limit headers included in responses:
  - `X-RateLimit-Limit`
  - `X-RateLimit-Remaining`
  - `X-RateLimit-Reset`

## Versioning

- API versioning through URL path (`/api/v1/`)
- Backward compatibility maintained
- Deprecation notices provided 6 months in advance

## Security

- HTTPS required
- JWT authentication
- Input validation
- SQL injection prevention
- XSS protection
- CORS configuration

## Best Practices

1. Use appropriate HTTP methods
2. Include proper headers
3. Handle errors consistently
4. Validate input data
5. Implement proper authentication
6. Use pagination for large datasets
7. Maintain versioning
8. Document all endpoints
9. Include rate limiting
10. Implement proper logging
