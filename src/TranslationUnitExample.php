<?php
require_once 'TranslationUnit.php';
require_once __DIR__ . '/../api/database_connection.php';

// Initialize database connection
$translationUnit = new TranslationUnit($pdo);

// 1. Add a new translation unit
$createResult = $translationUnit->create("Welcome to our platform", 1); // 1 = English
echo "Created Translation Unit ID: " . $createResult['id'] . PHP_EOL;

// 2. Retrieve translation unit by ID
$unit = $translationUnit->getById($createResult['id']);
echo "Retrieved Unit: " . $unit['source'] . " (" . $unit['source_language_id'] . ")" . PHP_EOL;

// 3. Update the translation unit (this also records the previous version in history)
$updateResult = $translationUnit->update($createResult['id'], "Welcome to our amazing platform", 1);
echo $updateResult['message'] . PHP_EOL;

// 4. Retrieve again to confirm update
$updatedUnit = $translationUnit->getById($createResult['id']);
echo "Updated Unit: " . $updatedUnit['source'] . PHP_EOL;
