<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/TranslationUnit.php';

class TranslationUnitTest extends TestCase
{
    private $tu;

    protected function setUp(): void
    {
        $this->tu = new TranslationUnit();
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    private function cleanUp()
    {
        $pdo = new PDO("mysql:host=localhost;dbname=cat", "root", "");
        $pdo->exec("DELETE FROM translation_history");
        $pdo->exec("DELETE FROM translations");
        $pdo->exec("DELETE FROM translation_units");
    }

    public function testFindOrCreateUnit()
    {
        $unit = $this->tu->findOrCreateUnit("Hello", "en");
        $this->assertArrayHasKey('id', $unit);

        $unit2 = $this->tu->findOrCreateUnit("Hello", "en");
        $this->assertEquals($unit['id'], $unit2['id']);
    }

    public function testAddTranslationToUnit()
    {
        $unit = $this->tu->findOrCreateUnit("Good morning", "en");
        $translationId = $this->tu->addTranslationToUnit($unit['id'], "Bonjour", "fr");

        $this->assertIsInt($translationId);
    }

    public function testGetAllUnits()
    {
        $unit = $this->tu->findOrCreateUnit("Bye", "en");
        $this->tu->addTranslationToUnit($unit['id'], "Au revoir", "fr");

        $units = $this->tu->getAllUnits();
        $this->assertNotEmpty($units);
    }

    public function testGetUnitById()
    {
        $unit = $this->tu->findOrCreateUnit("See you", "en");
        $this->tu->addTranslationToUnit($unit['id'], "À bientôt", "fr");

        $data = $this->tu->getUnitById($unit['id']);
        $this->assertNotEmpty($data);
        $this->assertEquals($unit['id'], $data[0]['unit_id']);
    }

    public function testDeleteUnit()
    {
        $unit = $this->tu->findOrCreateUnit("Thanks", "en");
        $this->tu->addTranslationToUnit($unit['id'], "Merci", "fr");

        $deleted = $this->tu->deleteUnit($unit['id']);
        $this->assertTrue($deleted);

        $data = $this->tu->getUnitById($unit['id']);
        $this->assertEmpty($data);
    }

    public function testTranslationHistoryTracking()
    {
        $unit = $this->tu->findOrCreateUnit("Good evening", "en");
        $translationId = $this->tu->addTranslationToUnit($unit['id'], "Bonsoir", "fr");

        $this->tu->updateUnitAndTranslation($unit['id'], $translationId, "Good evening", "en", "Bonne soirée", "fr");

        $history = $this->tu->getTranslationHistory($translationId);
        $this->assertNotEmpty($history);
        $this->assertEquals("Bonsoir", $history[0]['translated']);
        $this->assertEquals(1, (int)$history[0]['version']);
    }
}