# **Robert Cat Tool**

## **Overview**

Welcome to the **Robert Cat Tool** project! This is a **Core PHP** backend application for managing translations, translation units, and languages, with a **ReactJS** frontend. The backend uses **PHP** and **MySQL** for data management, while the frontend is built with **ReactJS** to provide a dynamic and interactive user interface. This README will guide you through setting up the project, configuring the environment, and running unit tests.

---

## **Table of Contents**

1. [Introduction](#introduction)
2. [Requirements](#requirements)
3. [Setting Up the Project Locally](#setting-up-the-project-locally)

---

## **Introduction**

The **Robert Cat Tool** is an application that combines a **Core PHP** backend with a **ReactJS** frontend for managing translations. Features include:

- Managing translation units
- Storing translation history
- Language management
- Interactive UI with ReactJS
- Running unit tests using PHPUnit

The application is built using **PHP**, **MySQL**, **ReactJS**, and a basic file-based configuration system.

---

## **Requirements**

Before you begin, make sure you have the following installed:

- **PHP** (version 8.1 or higher)
- **MySQL** or **MariaDB**
- **Composer** (for PHP dependency management)
- **Node.js** (for ReactJS frontend)
- **npm** or **Yarn** (for managing frontend dependencies)
- **Git** (for version control)
- **PHPUnit** (for testing)

---

## **Setting Up the Project Locally**

### 1. Clone the Repository

Start by cloning the repository to your local machine:

```bash
git clone https://github.com/strategic-agenda/robert-php-dev-test.git
```

### 2. Navigate to the Project Directory

After downloading or cloning the project, navigate to the project directory using the terminal:

```bash
cd robert-php-dev-test
```

### 3. Set Up Environment Configuration & Database

Before running the application, you need to set up your MySQL database. You can create the database manually or use the SQL dump provided.

#### 3.1 Set up your database configuration
Inside the project directory, you'll find the database/schema.sql file, which contains the necessary structure for the application’s database and you need to import that file.

#### 3.2 Set up the MySQL Database
Inside the config/ folder, you will find the config.php file. Open this file and update it with your database credentials:

```php
return [
  'host' => 'localhost', // host
  'dbname' => 'robert-cat-tool', // database name
  'username' => 'your_database_user', // username
  'password' => 'your_database_password', // password
];
```

### 4. Set Up the Frontend (ReactJS)

#### Requirements:
- Node.js (v20.x or higher)
- npm (Node Package Manager)

#### Steps:

##### Install Node.js dependencies:
1. Navigate to the `frontend/` directory:
   ```bash
   cd frontend
    ```
2. Install the required Node.js dependencies:
    ```bash
   npm install
    ```

##### Run the React Development Server:
1. After installing the dependencies, you can run the React development server:
     ```bash
   npm start
    ```
    This will start the React application at http://localhost:3000.

### 5. Running Tests (Optional)
### PHPUnit for Backend

The project uses PHPUnit for testing the PHP backend.

#### To run the tests:

From the root directory of the project, execute the following command:
```bash
./vendor/bin/phpunit tests/
```
