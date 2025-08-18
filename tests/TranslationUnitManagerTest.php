<?php

use PHPUnit\Framework\TestCase;

require_once 'src/TranslationUnit.php';
require_once 'src/TranslationUnitManager.php';

class TranslationUnitManagerTest extends TestCase
{
    private TranslationUnitManager $manager;

    protected function setUp(): void
    {
        $this->manager = new TranslationUnitManager();
    }

    public function testAddAndGetUnit()
    {
        $id = $this->manager->addUnit("Hello", "Olá", "en", "pt");
        $unit = $this->manager->getUnit($id);

        $this->assertInstanceOf(TranslationUnit::class, $unit);
        $this->assertEquals("Hello", $unit->sourceText);
    }

    public function testUpdateUnit()
    {
        $id = $this->manager->addUnit("Bye", "Tchau", "en", "pt");
        $this->manager->updateUnit($id, "Bye", "Adeus");

        $unit = $this->manager->getUnit($id);
        $this->assertEquals("Adeus", $unit->translatedText);
    }
}
