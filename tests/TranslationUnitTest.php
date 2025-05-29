<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/TranslationUnit.php';

class TranslationUnitTest extends TestCase
{
    private PDO $db;
    private TranslationUnit $unit;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables
        $this->db->exec('
            CREATE TABLE translation_units (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                source_text TEXT NOT NULL,
                target_text TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');
        
        $this->db->exec('
            CREATE TABLE translation_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                unit_id INTEGER,
                previous_target_text TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (unit_id) REFERENCES translation_units(id)
            )
        ');

        $this->unit = new TranslationUnit($this->db);
    }

    public function testAddTranslationUnit()
    {
        $id = $this->unit->add('Hello', 'Bonjour');
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $unit = $this->unit->get($id);
        $this->assertEquals('Hello', $unit['source_text']);
        $this->assertEquals('Bonjour', $unit['target_text']);
    }

    public function testGetNonExistentUnit()
    {
        $unit = $this->unit->get(999);
        $this->assertNull($unit);
    }

    public function testUpdateTranslationUnit()
    {
        $id = $this->unit->add('Hello', 'Bonjour');
        $success = $this->unit->update($id, 'Salut');
        
        $this->assertTrue($success);
        
        $unit = $this->unit->get($id);
        $this->assertEquals('Salut', $unit['target_text']);

        // Check history
        $history = $this->unit->getHistory($id);
        $this->assertCount(1, $history);
        $this->assertEquals('Bonjour', $history[0]['previous_target_text']);
    }

    public function testGetAllTranslationUnits()
    {
        $this->unit->add('Hello', 'Bonjour');
        $this->unit->add('Goodbye', 'Au revoir');

        $units = $this->unit->getAll();
        $this->assertCount(2, $units);
    }

    public function testDeleteTranslationUnit()
    {
        $id = $this->unit->add('Hello', 'Bonjour');
        $success = $this->unit->delete($id);
        
        $this->assertTrue($success);
        $this->assertNull($this->unit->get($id));
    }
}
