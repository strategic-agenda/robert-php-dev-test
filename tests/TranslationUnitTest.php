<?php

use PHPUnit\Framework\TestCase;
use App\Model\TranslationUnit;

class TranslationUnitTest extends TestCase
{
    private TranslationUnit $unit;

    protected function setUp(): void
    {
        $this->unit = new TranslationUnit(
            'Hello world',
            'Hola mundo',
            'en',
            'es'
        );
    }

    public function testCreateTranslationUnit(): void
    {
        $this->assertEquals('Hello world', $this->unit->getSourceText());
        $this->assertEquals('Hola mundo', $this->unit->getTargetText());
        $this->assertEquals('en', $this->unit->getSourceLanguage());
        $this->assertEquals('es', $this->unit->getTargetLanguage());
    }

    public function testUpdateTargetText(): void
    {
        $this->unit->setTargetText('¡Hola mundo!');
        $this->assertEquals('¡Hola mundo!', $this->unit->getTargetText());
    }

    public function testHistoryTracking(): void
    {
        // Initial state should have one history entry
        $history = $this->unit->getHistory();
        $this->assertCount(1, $history);
        $this->assertEquals('Hola mundo', $history[0]['targetText']);

        // Update target text should add new history entry
        $this->unit->setTargetText('¡Hola mundo!');
        $history = $this->unit->getHistory();
        $this->assertCount(2, $history);
        $this->assertEquals('¡Hola mundo!', $history[0]['targetText']);
        $this->assertEquals('Hola mundo', $history[1]['targetText']);
    }

    public function testToArray(): void
    {
        $array = $this->unit->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('Hello world', $array['sourceText']);
        $this->assertEquals('Hola mundo', $array['targetText']);
        $this->assertEquals('en', $array['sourceLanguage']);
        $this->assertEquals('es', $array['targetLanguage']);
        $this->assertArrayHasKey('createdAt', $array);
        $this->assertArrayHasKey('updatedAt', $array);
        $this->assertArrayHasKey('history', $array);
        $this->assertIsArray($array['history']);
    }

    public function testIdManagement(): void
    {
        $this->assertNull($this->unit->getId());
        
        $this->unit->setId(1);
        $this->assertEquals(1, $this->unit->getId());
    }

    public function testHistoryEntryFormat(): void
    {
        $history = $this->unit->getHistory();
        $entry = $history[0];

        $this->assertArrayHasKey('targetText', $entry);
        $this->assertArrayHasKey('updatedAt', $entry);
        $this->assertEquals('Hola mundo', $entry['targetText']);
        $this->assertIsString($entry['updatedAt']);
    }

    public function testMultipleUpdates(): void
    {
        $this->unit->setTargetText('First update');
        $this->unit->setTargetText('Second update');
        $this->unit->setTargetText('Final update');

        $history = $this->unit->getHistory();
        $this->assertCount(4, $history);
        $this->assertEquals('Final update', $history[0]['targetText']);
        $this->assertEquals('Second update', $history[1]['targetText']);
        $this->assertEquals('First update', $history[2]['targetText']);
        $this->assertEquals('Hola mundo', $history[3]['targetText']);
    }
}
