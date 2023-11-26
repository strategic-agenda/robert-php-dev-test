<?php

namespace Core\Tests\Logic\Translation_unit;

include 'src';
use Core\Logic\Translation_unit\TranslationUnit;
use PHPUnit\Framework\TestCase;

class TranslationUnitTest extends TestCase
{
    private $translationUnit;

    public function setUp(): void
    {
        $this->translationUnit = new TranslationUnit();
    }

    public function testAddTranslationUnit()
    {
        $this->assertEquals(1, $this->translationUnit->AddTranslationUnit('Hello, world!', 1, 'Hola, test!'));
    }

    public function testGetTranslationUnit()
    {
        $this->assertEquals([
            'id' => 1,
            'text' => 'Hello, world!',
            'langId' => 1,
            'trans_text' => 'Hola, test!',
        ], $this->translationUnit->GetTranslationUnit(1));
    }

    public function testUpdateTranslationUnit()
    {
        $this->assertTrue($this->translationUnit->UpdateTranslationUnit(1, 'Buenos, test!'));
    }

    public function testDeleteTranslationUnit()
    {
        $this->assertTrue($this->translationUnit->DeleteTranslationUnit(1));
    }

    public function testGetAllTranslationUnits()
    {
        $this->assertEquals([
            [
                'id' => 1,
                'text' => 'Hello, world!',
                'langId' => 1,
                'trans_text' => 'Hola, test!',
            ],
        ], $this->translationUnit->GetAllTranslationUnits());
    }
}