<?php

use PHPUnit\Framework\TestCase;
require_once 'src/TranslationUnit.php';

class TranslationUnitTest extends TestCase
{
    public function testUpdateTranslationChangesTextAndTimestamp()
    {
        $unit = new TranslationUnit(1, "Hello", "Olá", "en", "pt");
        sleep(1);
        $unit->updateTranslation("Oi");

        $this->assertEquals("Oi", $unit->translatedText);
        $this->assertNotEquals($unit->createdAt, $unit->updatedAt);
    }
}
