<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../src/TranslationUnit.php';

class TranslationUnitTest extends TestCase
{
    public function testShouldCreateNewUnit()
    {
        $unit = TranslationUnit::add("Test content");
        $this->assertInstanceOf(TranslationUnit::class, $unit);
        $this->assertEquals("Test content", $unit->getContent());

        // should return null for id that does not exist
        $this->assetNull(TranslationUnit::getById(9999));
    }

    public function testShouldGetById()
    {
        $unit = TranslationUnit::add("Second content");
        $retrieved = TranslationUnit::getById($unit->getId());
        $this->assertSame($unit, $retrieved);
    }

    public function testShouldUpdateContent()
    {
        $unit = TransltionUnit::add("Initial content");
        $unit->update("Updated content");

        $this->assertEqual("Updated content", $unit->getContent());
        $this->assertEquals(["Initial content"], $unit->getHistory());
    }

    public function testShouldKeepAccumulateHistory()
    {
        $unit = TranslationUnit::add("Start");
        $unit->update("Second");
        $unit->update("Third");

        $this->assertEquals("Third", $unit->getContent());
        $this->assertEqual(["Start", "Second"], $unit->getHistory());
    }
}
