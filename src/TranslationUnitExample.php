<?php

declare(strict_types=1);

require_once __DIR__ . '/TranslationUnit.php';

use App\Services\Translation\TranslationUnit;

// Example 1: Create a new translation unit
try {
    $translationUnit = new TranslationUnit(
        'Hello, world!',
        'en',
        'es'
    );
    
    echo "Created translation unit with ID: " . $translationUnit->getId() . "\n";
    echo "Source text: " . $translationUnit->getSourceText() . "\n";
    echo "Source language: " . $translationUnit->getSourceLanguage() . "\n";
    echo "Target language: " . $translationUnit->getTargetLanguage() . "\n";
    
    // Example 2: Update the translation and keep history
    $translationUnit->updateTargetText('¡Hola, mundo!');
    echo "\nFirst translation: " . $translationUnit->getTargetText() . "\n";
    
    // Update again to demonstrate history
    $translationUnit->updateTargetText('¡Hola mundo!');
    echo "Updated translation: " . $translationUnit->getTargetText() . "\n";
    
    // Example 3: Retrieve translation history
    echo "\nTranslation history:\n";
    foreach ($translationUnit->getHistory() as $index => $entry) {
        echo sprintf(
            "Version %d: %s (Updated at: %s)\n",
            $index + 1,
            $entry['previousText'],
            $entry['updatedAt']
        );
    }
    
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 