<?php

require_once 'DatabaseTestCase.php';
require_once __DIR__ . '/../src/TranslationUnit.php';

class TranslationUnitTest extends DatabaseTestCase
{
    private $translationUnit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translationUnit = new TranslationUnit($this->pdo);
    }

    public function testCreateAndGetById(): void
    {
        $createResult = $this->translationUnit->create("Hello World", 1);
        $this->assertIsArray($createResult);
        $this->assertArrayHasKey('id', $createResult);

        $fetched = $this->translationUnit->getById($createResult['id']);
        $this->assertEquals("Hello World", $fetched['source']);
        $this->assertEquals(1, $fetched['source_language_id']);
    }

    public function testGetAll(): void
    {
        $this->translationUnit->create("Test A", 1);
        $this->translationUnit->create("Test B", 1);

        $all = $this->translationUnit->getAll();
        $this->assertGreaterThanOrEqual(2, count($all));

        $sources = array_column($all, 'source');
        $this->assertContains("Test A", $sources);
        $this->assertContains("Test B", $sources);
    }

    public function testUpdate()
    {
        $result = $this->translationUnit->create('Initial Text', 1);
        $id = $result['id'];

        $update = $this->translationUnit->update($id, 'Updated Text', 1);
        $this->assertEquals('Translation Unit has been updated and history stored.', $update['message']);

        $fetched = $this->translationUnit->getById($id);
        $this->assertEquals('Updated Text', $fetched['source']);
    }

    public function testDelete()
    {
        $result = $this->translationUnit->create('To be deleted', 1);
        $id = $result['id'];

        $delete = $this->translationUnit->delete($id);
        $this->assertEquals('Translation Unit and associated translations have been deleted.', $delete['message']);

        $fetched = $this->translationUnit->getById($id);
        $this->assertFalse($fetched);
    }
}
