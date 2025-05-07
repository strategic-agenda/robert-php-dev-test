<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../api/src/TranslationUnit.php';

class TranslationUnitTest extends TestCase
{
    private $translationUnit;
    
    protected function setUp(): void
    {
        $this->translationUnit = new TranslationUnit(
            'Hello world',
            'Bonjour le monde',
            'en',
            'fr'
        );
    }
    
    public function testConstructor()
    {
        $this->assertEquals('Hello world', $this->translationUnit->getSourceText());
        $this->assertEquals('Bonjour le monde', $this->translationUnit->getTargetText());
    }
    
    public function testUpdateTargetText()
    {
        $newText = 'Bonjour tout le monde';
        $this->translationUnit->setId(1); // Mock ID for testing
        
        // Mock the database connection for testing
        $this->translationUnit = $this->getMockBuilder(TranslationUnit::class)
            ->setMethods(['saveHistory', 'update'])
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->translationUnit->method('saveHistory')->willReturn(true);
        $this->translationUnit->method('update')->willReturn($this->translationUnit);
        
        // Set properties manually
        $reflectionClass = new ReflectionClass(TranslationUnit::class);
        
        $sourceTextProperty = $reflectionClass->getProperty('sourceText');
        $sourceTextProperty->setAccessible(true);
        $sourceTextProperty->setValue($this->translationUnit, 'Hello world');
        
        $targetTextProperty = $reflectionClass->getProperty('targetText');
        $targetTextProperty->setAccessible(true);
        $targetTextProperty->setValue($this->translationUnit, 'Bonjour le monde');
        
        // Call the method
        $this->translationUnit->updateTargetText($newText);
        
        // Assert the result
        $this->assertEquals($newText, $this->translationUnit->getTargetText());
    }
    
    public function testToArray()
    {
        $this->translationUnit->setId(1);
        
        // Mock the getHistory method
        $this->translationUnit = $this->getMockBuilder(TranslationUnit::class)
            ->setMethods(['getHistory'])
            ->disableOriginalConstructor()
            ->getMock();
            
        $history = [
            [
                'target_text' => 'Bonjour le monde',
                'updated_at' => '2023-01-01 12:00:00'
            ]
        ];
        
        $this->translationUnit->method('getHistory')->willReturn($history);
        
        // Set properties manually
        $reflectionClass = new ReflectionClass(TranslationUnit::class);
        
        $idProperty = $reflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->translationUnit, 1);
        
        $sourceTextProperty = $reflectionClass->getProperty('sourceText');
        $sourceTextProperty->setAccessible(true);
        $sourceTextProperty->setValue($this->translationUnit, 'Hello world');
        
        $targetTextProperty = $reflectionClass->getProperty('targetText');
        $targetTextProperty->setAccessible(true);
        $targetTextProperty->setValue($this->translationUnit, 'Bonjour le monde');
        
        $sourceLanguageProperty = $reflectionClass->getProperty('sourceLanguage');
        $sourceLanguageProperty->setAccessible(true);
        $sourceLanguageProperty->setValue($this->translationUnit, 'en');
        
        $targetLanguageProperty = $reflectionClass->getProperty('targetLanguage');
        $targetLanguageProperty->setAccessible(true);
        $targetLanguageProperty->setValue($this->translationUnit, 'fr');
        
        $createdAtProperty = $reflectionClass->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($this->translationUnit, '2023-01-01 12:00:00');
        
        $updatedAtProperty = $reflectionClass->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($this->translationUnit, '2023-01-01 12:00:00');
        
        $array = $this->translationUnit->toArray();
        
        $this->assertEquals(1, $array['id']);
        $this->assertEquals('Hello world', $array['sourceText']);
        $this->assertEquals('Bonjour le monde', $array['targetText']);
        $this->assertEquals('en', $array['sourceLanguage']);
        $this->assertEquals('fr', $array['targetLanguage']);
        $this->assertEquals('2023-01-01 12:00:00', $array['createdAt']);
        $this->assertEquals('2023-01-01 12:00:00', $array['updatedAt']);
        $this->assertEquals($history, $array['history']);
    }
}
