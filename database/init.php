<?php

try {
    $db = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create translation_units table
    $db->exec('
        CREATE TABLE IF NOT EXISTS translation_units (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            source_text TEXT NOT NULL,
            target_text TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    // Create translation_history table
    $db->exec('
        CREATE TABLE IF NOT EXISTS translation_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            unit_id INTEGER,
            previous_target_text TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (unit_id) REFERENCES translation_units(id)
        )
    ');

    echo "Database tables created successfully!\n";

    // Insert some sample data
    $db->exec("
        INSERT INTO translation_units (source_text, target_text) VALUES 
        ('Hello', 'Bonjour'),
        ('Goodbye', 'Au revoir'),
        ('Thank you', 'Merci'),
        ('How are you?', 'Comment allez-vous?')
    ");

    echo "Sample data inserted successfully!\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 