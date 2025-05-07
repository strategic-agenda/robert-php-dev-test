<?php
/**
 * Script to set up a test database for PHPUnit tests
 * 
 * Usage: php tests/setup-test-db.php
 */

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cat_db_test';

// Connect to MySQL without selecting a database
$dsn = "mysql:host=$host";
$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create database if it doesn't exist
try {
    echo "Creating test database '$database'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Test database created or already exists.\n";
} catch (PDOException $e) {
    die("Error creating database: " . $e->getMessage() . "\n");
}

// Select the test database
$pdo->exec("USE `$database`");

// Create tables
try {
    echo "Creating tables...\n";
    
    // Languages table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `languages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `code` varchar(10) NOT NULL,
            `name` varchar(50) NOT NULL,
            `direction` enum('ltr','rtl') NOT NULL DEFAULT 'ltr',
            `active` tinyint(1) NOT NULL DEFAULT '1',
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Translation units table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `translation_units` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `source_text` text NOT NULL,
            `target_text` text NOT NULL,
            `source_language` varchar(10) NOT NULL,
            `target_language` varchar(10) NOT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Translation history table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `translation_history` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `translation_unit_id` int(11) NOT NULL,
            `target_text` text NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `translation_unit_id` (`translation_unit_id`),
            CONSTRAINT `translation_history_ibfk_1` FOREIGN KEY (`translation_unit_id`) REFERENCES `translation_units` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "Tables created successfully.\n";
} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage() . "\n");
}

// Insert some sample data
try {
    echo "Inserting sample data...\n";
    
    // Insert languages
    $languages = [
        ['en', 'English'],
        ['fr', 'French'],
        ['es', 'Spanish'],
        ['de', 'German'],
        ['it', 'Italian']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO `languages` (`code`, `name`, `direction`, `active`, `created_at`)
        VALUES (?, ?, 'ltr', 1, NOW())
        ON DUPLICATE KEY UPDATE `name` = VALUES(`name`)
    ");
    
    foreach ($languages as $language) {
        $stmt->execute($language);
    }
    
    echo "Sample data inserted successfully.\n";
    echo "\nTest database setup complete! You can now run integration tests.\n";
    
} catch (PDOException $e) {
    die("Error inserting sample data: " . $e->getMessage() . "\n");
} 