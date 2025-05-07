# API Endpoints Documentation

This document provides detailed information about the RESTful API endpoints available for the Robert Computer-Assisted Translation (CAT) tool. These endpoints allow you to perform CRUD operations on translation units.

## Base URL

All API endpoints are accessed relative to: `/api`

## Authentication

Currently, the API does not require authentication.

## Data Format

All requests and responses use JSON format.

## API Endpoints

### List All Translation Units

Returns a list of all translation units in the system.

- **URL**: `/translations`
- **Method**: `GET`
- **URL Parameters**: None
- **Success Response**:
  - **Code**: 200 OK
  - **Content Example**:
  ```json
  [
    {
      "id": 1,
      "source_text": "Hello world",
      "translations": {
        "fr": "Bonjour le monde",
        "es": "Hola mundo"
      },
      "created_at": "2023-01-01T10:00:00",
      "updated_at": "2023-01-01T10:00:00"
    },
    {
      "id": 2,
      "source_text": "Welcome to Robert",
      "translations": {
        "fr": "Bienvenue à Robert"
      },
      "created_at": "2023-01-02T11:30:00",
      "updated_at": "2023-01-02T11:30:00"
    }
  ]
  ```
- **Error Response**:
  - **Code**: 500 Internal Server Error
  - **Content**:
  ```json
  {
    "error": "Error message"
  }
  ```

### Retrieve a Specific Translation Unit

Returns a single translation unit identified by its ID.

- **URL**: `/translations/{id}` or `/translations?id={id}`
- **Method**: `GET`
- **URL Parameters**:
  - `id` (required): ID of the translation unit to retrieve
- **Success Response**:
  - **Code**: 200 OK
  - **Content Example**:
  ```json
  {
    "id": 1,
    "source_text": "Hello world",
    "translations": {
      "fr": "Bonjour le monde",
      "es": "Hola mundo"
    },
    "created_at": "2023-01-01T10:00:00",
    "updated_at": "2023-01-01T10:00:00"
  }
  ```
- **Error Response**:
  - **Code**: 404 Not Found
  - **Content**:
  ```json
  {
    "error": "Translation unit not found"
  }
  ```

### Create a New Translation Unit

Creates a new translation unit with the provided source text and optional translations.

- **URL**: `/translations`
- **Method**: `POST`
- **Headers**:
  - `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "source_text": "Hello world",
    "translations": {
      "fr": "Bonjour le monde",
      "es": "Hola mundo"
    }
  }
  ```
- **Required Fields**:
  - `source_text`: Original text to be translated
- **Optional Fields**:
  - `translations`: Object with language codes as keys and translated text as values
- **Success Response**:
  - **Code**: 201 Created
  - **Content Example**:
  ```json
  {
    "id": 3,
    "source_text": "Hello world",
    "translations": {
      "fr": "Bonjour le monde",
      "es": "Hola mundo"
    },
    "created_at": "2023-01-03T09:45:00",
    "updated_at": "2023-01-03T09:45:00"
  }
  ```
- **Error Responses**:
  - **Code**: 400 Bad Request
  - **Content**:
  ```json
  {
    "error": "Missing required fields: source_text"
  }
  ```
  - **Code**: 409 Conflict
  - **Content**:
  ```json
  {
    "error": "Failed to create translation unit"
  }
  ```

### Update a Translation Unit

Updates an existing translation unit by ID.

- **URL**: `/translations`
- **Method**: `PUT`
- **Headers**:
  - `Content-Type: application/json`
- **Request Body**:
  ```json
  {
    "id": 1,
    "source_text": "Updated hello world",
    "translations": {
      "fr": "Bonjour le monde mis à jour",
      "es": "Hola mundo actualizado",
      "de": "Hallo Welt"
    }
  }
  ```
- **Required Fields**:
  - `id`: ID of the translation unit to update
- **Optional Fields**:
  - `source_text`: Updated source text (omit to keep current)
  - `translations`: Updated translations (omit to keep current)
- **Success Response**:
  - **Code**: 200 OK
  - **Content Example**:
  ```json
  {
    "id": 1,
    "source_text": "Updated hello world",
    "translations": {
      "fr": "Bonjour le monde mis à jour",
      "es": "Hola mundo actualizado",
      "de": "Hallo Welt"
    },
    "created_at": "2023-01-01T10:00:00",
    "updated_at": "2023-01-04T14:20:00"
  }
  ```
- **Error Responses**:
  - **Code**: 400 Bad Request
  - **Content**:
  ```json
  {
    "error": "Missing required field: id is required"
  }
  ```
  - **Code**: 404 Not Found
  - **Content**:
  ```json
  {
    "error": "Translation unit not found"
  }
  ```

### Delete a Translation Unit

Deletes a translation unit by ID.

- **URL**: `/translations/{id}` or `/translations?id={id}`
- **Method**: `DELETE`
- **URL Parameters**:
  - `id` (required): ID of the translation unit to delete
- **Success Response**:
  - **Code**: 200 OK
  - **Content**:
  ```json
  {
    "message": "Translation unit deleted successfully"
  }
  ```
- **Error Responses**:
  - **Code**: 400 Bad Request
  - **Content**:
  ```json
  {
    "error": "Missing required parameter: id"
  }
  ```
  - **Code**: 404 Not Found
  - **Content**:
  ```json
  {
    "error": "Translation unit not found"
  }
  ```
