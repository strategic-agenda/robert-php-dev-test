# Translation Management System

A full-stack application for managing translations with history tracking.

## Backend Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Copy the environment file:

```bash
cp .env.example .env
```

3. Start the PHP development server:

```bash
php -S localhost:8000 -t public
```

## Frontend Setup

1. Navigate to the frontend directory:

```bash
cd frontend
```

2. Install Node.js dependencies:

```bash
npm install
```

3. Start the development server:

```bash
npm start
```

The frontend will be available at http://localhost:3000

## Running Tests

To run the backend tests:

```bash
composer test
```

To run the frontend tests:

```bash
cd frontend
npm test
```

## Features

- Create, read, update, and delete translations
- Track translation history
- Modern React frontend with responsive design
- RESTful API backend
- Comprehensive test coverage

## API Endpoints

- GET /api/translations - List all translations
- GET /api/translations/{id} - Get a specific translation
- POST /api/translations - Create a new translation
- PUT /api/translations/{id} - Update a translation
- DELETE /api/translations/{id} - Delete a translation
