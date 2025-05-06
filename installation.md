# Robert CAT Tool - Installation Guide

This guide will help you set up and run the Robert Computer-Assisted Translation (CAT) Tool on your local machine.

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer
- Node.js (v16+) and npm

## Project Structure

The project is divided into two main parts:

- Backend (PHP) - Provides the API for translation management
- Frontend (React/TypeScript) - User interface for the translation tool

## Installation Steps

### 1. Clone the Repository

```bash
git clone <repository-url>
cd robert-cat-tool
```

### 2. Set Up the Database

```bash
# Log in to MySQL
mysql -u root -p

# In MySQL prompt, run:
CREATE DATABASE robert_cat;
CREATE USER 'robert_user'@'localhost' IDENTIFIED BY 'R0b3rt_P@ssword!';
GRANT ALL PRIVILEGES ON robert_cat.* TO 'robert_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import the database schema
mysql -u robert_user -p robert_cat < database/schema.sql
# Enter password when prompted: R0b3rt_P@ssword!
```

### 3. Set Up the Backend

```bash
# Install PHP dependencies
composer install
composer dump-autoload

# Start the PHP development server
php -S localhost:8000 -t .
```

The API will be available at http://localhost:8000/api/

### 4. Set Up the Frontend

In a new terminal window:

```bash
# Navigate to the frontend directory
cd frontend

# Install dependencies
npm install

# Start the development server
npm run dev
```

The frontend application will be available at http://localhost:5173/ (or another port if 5173 is busy)

### 5. Running Tests

The project includes PHPUnit tests for the TranslationUnit class. To run these tests:

```bash
# From the project root directory
./vendor/bin/phpunit tests/TranslationUnitTest.php
```

This will execute all the test cases defined in the TranslationUnitTest class and display the results in your terminal.

## Verify the Installation

1. Open your browser and navigate to http://localhost:5173/
2. You should see the Robert CAT Tool interface
3. Select a document and target language from the dropdown menus
4. The translation units should load, and you can start translating

## Troubleshooting

### Database Connection Issues

If you encounter database connection problems:

1. Verify your MySQL service is running
2. Check the credentials in `api/translations.php` match what you set up
3. Ensure the database exists and has the correct schema

### API Connection Issues

If the frontend can't connect to the API:

1. Make sure the PHP server is running on port 8000
2. Check the `API_URL` constant in `frontend/src/services/translationService.ts`
3. Look for CORS errors in the browser console (the API includes CORS headers)

### Frontend Build Issues

If the frontend fails to build or run:

1. Check your Node.js version (use v16 or higher)
2. Clear your npm cache: `npm cache clean --force`
3. Delete `node_modules` and reinstall: `rm -rf node_modules && npm install`

### Test Execution Issues

If you encounter problems running the tests:

1. Ensure PHPUnit is properly installed via Composer
2. Verify the test file path is correct relative to your project root
3. Check that your PHP version is compatible with the PHPUnit version

## Project Access

Once both servers are running:

- Backend API: http://localhost:8000/api/translations
- Frontend application: http://localhost:5173/

## Database Credentials (for development only)

- Username: robert_user
- Password: R0b3rt_P@ssword!
- Database name: robert_cat
