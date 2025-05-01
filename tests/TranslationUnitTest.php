<?php

use PHPUnit\Framework\TestCase;

class TranslationUnitTest extends TestCase
{
    private $translationUnit;

    protected function setUp(): void
    {
        $this->translationUnit = new TranslationUnit('unit_001', 'Hello, world!', 'en');
    }

    public function testCanCreateTranslationUnit(): void
    {
        $details = $this->translationUnit->getDetails();

        $this->assertEquals('unit_001', $details['id']);
        $this->assertEquals('Hello, world!', $details['source_text']);
        $this->assertEquals('en', $details['source_language']);
        $this->assertEmpty($details['translations']);
        $this->assertEmpty($details['history']);
    }

    public function testCanAddTranslation(): void
    {
        $result = $this->translationUnit->addTranslation('es', '¡Hola, mundo!', 'translator_001');
        $details = $this->translationUnit->getDetails();

        $this->assertTrue($result);
        $this->assertArrayHasKey('es', $details['translations']);
        $this->assertEquals('¡Hola, mundo!', $details['translations']['es']['text']);
        $this->assertEquals('translator_001', $details['translations']['es']['translator_id']);
        $this->assertCount(1, $details['history']);
        $this->assertEquals('add', $details['history'][0]['action']);
        $this->assertEquals('es', $details['history'][0]['target_language']);
        $this->assertEquals('¡Hola, mundo!', $details['history'][0]['text']);
    }

    public function testCannotAddDuplicateTranslation(): void
    {
        $this->translationUnit->addTranslation('es', '¡Hola, mundo!', 'translator_001');
        $result = $this->translationUnit->addTranslation('es', '¡Hola, mundo nuevo!', 'translator_002');
        $details = $this->translationUnit->getDetails();

        $this->assertFalse($result);
        $this->assertEquals('¡Hola, mundo!', $details['translations']['es']['text']);
        $this->assertCount(1, $details['history']);
    }

    public function testCanUpdateTranslation(): void
    {
        $this->translationUnit->addTranslation('es', '¡Hola, mundo!', 'translator_001');
        $result = $this->translationUnit->updateTranslation('es', '¡Hola, mundo nuevo!', 'translator_002', 'Improved accuracy');
        $details = $this->translationUnit->getDetails();

        $this->assertTrue($result);
        $this->assertEquals('¡Hola, mundo nuevo!', $details['translations']['es']['text']);
        $this->assertEquals('translator_002', $details['translations']['es']['translator_id']);
        $this->assertCount(2, $details['history']);
        $this->assertEquals('update', $details['history'][1]['action']);
        $this->assertEquals('¡Hola, mundo!', $details['history'][1]['previous_text']);
        $this->assertEquals('¡Hola, mundo nuevo!', $details['history'][1]['new_text']);
        $this->assertEquals('Improved accuracy', $details['history'][1]['reason']);
    }

    public function testCannotUpdateNonExistentTranslation(): void
    {
        $result = $this->translationUnit->updateTranslation('fr', 'Bonjour le monde!', 'translator_002');
        $details = $this->translationUnit->getDetails();

        $this->assertFalse($result);
        $this->assertArrayNotHasKey('fr', $details['translations']);
        $this->assertEmpty($details['history']);
    }

    public function testHistoryIsMaintained(): void
    {
        $this->translationUnit->addTranslation('es', '¡Hola, mundo!', 'translator_001');
        $this->translationUnit->updateTranslation('es', '¡Hola, mundo nuevo!', 'translator_002', 'Improved accuracy');
        $history = $this->translationUnit->getHistory();

        $this->assertCount(2, $history);
        $this->assertEquals('add', $history[0]['action']);
        $this->assertEquals('update', $history[1]['action']);
        $this->assertEquals('translator_002', $history[1]['translator_id']);
        $this->assertArrayHasKey('timestamp', $history[1]);
    }
}
?>