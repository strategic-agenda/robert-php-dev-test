# Computer-Assisted Translation (CAT) Tool

A web-based translation tool that helps translators work more efficiently by managing translation units.

## Features

- Create, read, update, and delete translation units
- Track the history of translation changes
- User-friendly interface for managing translations
- RESTful API for integration with other systems

## Project Structure

```
robert-php-dev-test/
├── api/                  # Backend PHP API
│   ├── public/           # Public entry point
│   ├── src/              # PHP classes
│   └── composer.json     # PHP dependencies
├── frontend/             # React frontend
│   ├── public/           # Static assets
│   └── src/              # React components
├── database/             # Database files
│   └── schema.sql        # Database schema
├── docs/                 # Documentation
│   └── architecture.md   # Architecture document
└── tests/                # Unit tests
```

## Technical Requirements

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Node.js 16.0 or higher
- Web server (Apache/Nginx)

## Setup and Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/robert-php-dev-test.git
cd robert-php-dev-test
```

### 2. Database Setup

Create a MySQL database and user:

```sql
CREATE DATABASE cat_db;
CREATE USER 'cat_user'@'localhost' IDENTIFIED BY 'cat_password';
GRANT ALL PRIVILEGES ON cat_db.* TO 'cat_user'@'localhost';
FLUSH PRIVILEGES;
```

Import the database schema:

```bash
mysql -u cat_user -p cat_db < database/schema.sql
```

### 3. Backend Setup

Navigate to the API directory and install dependencies:

```bash
cd api
composer install
```

Configure your web server to point to the `api/public` directory, or use PHP's built-in server for development:

```bash
php -S localhost:8080 -t public
```

### 4. Frontend Setup

Navigate to the frontend directory and install dependencies:

```bash
cd ../frontend
npm install
npm start
```

The frontend will be accessible at http://localhost:3000

## API Endpoints

- `GET /api/units`: Get all translation units
- `GET /api/units/{id}`: Get a specific translation unit
- `POST /api/units`: Create a new translation unit
- `PUT /api/units/{id}`: Update a translation unit
- `DELETE /api/units/{id}`: Delete a translation unit

## Running Tests

To run the unit tests:

```bash
cd api
vendor/bin/phpunit ../tests
```

## Implementation Details

### Design Patterns

The application implements several design patterns:

1. **Singleton Pattern**: For database connection management
2. **Repository Pattern**: For data access abstraction
3. **Active Record Pattern**: For translation unit management
4. **Observer Pattern**: For history tracking

See the [architecture document](architecture.md) for more details on the design patterns and architecture.

## Author

Your Name - your.email@example.com 