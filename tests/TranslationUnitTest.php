<?php

use PHPUnit\Framework\TestCase;
use Robert\CAT\TranslationUnit;

class TranslationUnitTest extends TestCase
{
    /**
     * Test creating a new translation unit
     */
    public function testCreateTranslationUnit()
    {
        $documentId = 1;
        $sequenceNumber = 5;
        $sourceContent = 'This is a test source content.';

        $unit = new TranslationUnit($documentId, $sequenceNumber, $sourceContent);

        $this->assertEquals($documentId, $unit->getDocumentId());
        $this->assertEquals($sequenceNumber, $unit->getSequenceNumber());
        $this->assertEquals($sourceContent, $unit->getSourceContent());
        $this->assertNull($unit->getId());
        $this->assertNull($unit->getContext());
        $this->assertEmpty($unit->getTranslations());
    }

    /**
     * Test setting and getting unit ID
     */
    public function testSetAndGetId()
    {
        $unit = new TranslationUnit(1, 1, 'Test content');
        $id = 42;

        $this->assertNull($unit->getId());

        $unit->setId($id);
        $this->assertEquals($id, $unit->getId());
    }

    /**
     * Test setting and getting context
     */
    public function testSetAndGetContext()
    {
        $unit = new TranslationUnit(1, 1, 'Test content');
        $context = 'This is some context for the translator.';

        $this->assertNull($unit->getContext());

        $unit->setContext($context);
        $this->assertEquals($context, $unit->getContext());
    }

    /**
     * Test setting source content
     */
    public function testSetSourceContent()
    {
        $originalContent = 'Original source content.';
        $newContent = 'Updated source content.';

        $unit = new TranslationUnit(1, 1, $originalContent);

        // Update the source content
        $unit->setSourceContent($newContent);

        // Check if the content was updated
        $this->assertEquals($newContent, $unit->getSourceContent());
    }

    /**
     * Test adding a new translation
     */
    public function testAddNewTranslation()
    {
        $unit = new TranslationUnit(1, 1, 'Test source content.');
        $languageId = 2;  // Spanish
        $content = 'Contenido de prueba.';
        $translatedBy = 5;  // User ID

        $unit->addTranslation($languageId, $content, $translatedBy);

        $translations = $unit->getTranslations();
        $this->assertCount(1, $translations);
        $this->assertArrayHasKey($languageId, $translations);
        $this->assertEquals($content, $translations[$languageId]['content']);
        $this->assertEquals($translatedBy, $translations[$languageId]['translated_by']);
        $this->assertEquals('draft', $translations[$languageId]['status']);

        // Check that we can retrieve the translation
        $translation = $unit->getTranslation($languageId);
        $this->assertNotNull($translation);
        $this->assertEquals($content, $translation['content']);
    }

    /**
     * Test updating an existing translation
     */
    public function testUpdateExistingTranslation()
    {
        $unit = new TranslationUnit(1, 1, 'Test source content.');
        $languageId = 2;
        $originalContent = 'Original translation.';
        $updatedContent = 'Updated translation.';
        $translatedBy = 5;

        // Add the original translation
        $unit->addTranslation($languageId, $originalContent, $translatedBy);

        // Update the translation
        $unit->addTranslation($languageId, $updatedContent, $translatedBy);

        // Check if the translation was updated
        $translation = $unit->getTranslation($languageId);
        $this->assertEquals($updatedContent, $translation['content']);
    }

    /**
     * Test updating translation status
     */
    public function testUpdateTranslationStatus()
    {
        $unit = new TranslationUnit(1, 1, 'Test source content.');
        $languageId = 2;
        $content = 'Translated content.';
        $translatedBy = 5;
        $reviewedBy = 10;
        $newStatus = 'reviewed';

        // Add the translation
        $unit->addTranslation($languageId, $content, $translatedBy);

        // Update the status
        $unit->updateTranslationStatus($languageId, $newStatus, $reviewedBy);

        // Check if the status was updated
        $translation = $unit->getTranslation($languageId);
        $this->assertEquals($newStatus, $translation['status']);
        $this->assertEquals($reviewedBy, $translation['reviewed_by']);
    }

    /**
     * Test JSON serialization
     */
    public function testJsonSerialization()
    {
        $documentId = 1;
        $sequenceNumber = 5;
        $sourceContent = 'Test source content.';
        $context = 'Test context.';
        $id = 42;

        $unit = new TranslationUnit($documentId, $sequenceNumber, $sourceContent);
        $unit->setId($id);
        $unit->setContext($context);

        // Add a translation
        $languageId = 2;
        $content = 'Translated content.';
        $translatedBy = 5;
        $unit->addTranslation($languageId, $content, $translatedBy);

        // Test JSON serialization
        $json = json_encode($unit);
        $data = json_decode($json, true);

        $this->assertEquals($id, $data['id']);
        $this->assertEquals($documentId, $data['document_id']);
        $this->assertEquals($sequenceNumber, $data['sequence_number']);
        $this->assertEquals($sourceContent, $data['source_content']);
        $this->assertEquals($context, $data['context']);
        $this->assertArrayHasKey('translations', $data);
        $this->assertArrayHasKey($languageId, $data['translations']);
        $this->assertEquals($content, $data['translations'][$languageId]['content']);
    }

    /**
     * Test toArray method
     */
    public function testToArray()
    {
        $documentId = 1;
        $sequenceNumber = 5;
        $sourceContent = 'Test source content.';
        $context = 'Test context.';
        $id = 42;

        $unit = new TranslationUnit($documentId, $sequenceNumber, $sourceContent);
        $unit->setId($id);
        $unit->setContext($context);

        // Convert to array
        $data = $unit->toArray();

        $this->assertEquals($id, $data['id']);
        $this->assertEquals($documentId, $data['document_id']);
        $this->assertEquals($sequenceNumber, $data['sequence_number']);
        $this->assertEquals($sourceContent, $data['source_content']);
        $this->assertEquals($context, $data['context']);
        $this->assertArrayHasKey('translations', $data);
    }
}
