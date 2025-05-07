<?php

require_once 'DatabaseTestCase.php';
require_once __DIR__ . '/../src/Translation.php';
require_once __DIR__ . '/../src/TranslationUnit.php';

class TranslationTest extends DatabaseTestCase
{
    private Translation $translation;
    private TranslationUnit $translationUnit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translation = new Translation($this->pdo);
        $this->translationUnit = new TranslationUnit($this->pdo);
    }

    public function testCreateAndGetById(): void
    {
        $unitId = $this->translationUnit->create("Test text", 1)['id'];
        $result = $this->translation->create($unitId, "Texte de test", 2);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);

        $fetched = $this->translation->getById($result['id']);
        $this->assertEquals("Texte de test", $fetched['translated_text']);
        $this->assertEquals("French", $fetched['translated_language_name']);
    }

    public function testGetAllByUnitId(): void
    {
        $unitId = $this->translationUnit->create("Unit with translations", 1)['id'];
        $this->translation->create($unitId, "Traduction A", 2);
        $this->translation->create($unitId, "Traduction B", 2);

        $all = $this->translation->getAllByUnitId($unitId);
        $this->assertCount(2, $all);

        $texts = array_column($all, 'translated_text');
        $this->assertContains("Traduction A", $texts);
        $this->assertContains("Traduction B", $texts);
    }

    public function testUpdate(): void
    {
        $unitId = $this->translationUnit->create("Unit for update", 1)['id'];
        $create = $this->translation->create($unitId, "Old Text", 2);
        $id = $create['id'];

        $update = $this->translation->update($id, "Updated Text", 2);
        $this->assertEquals("Translation updated and history stored.", $update['message']);

        $updated = $this->translation->getById($id);
        $this->assertEquals("Updated Text", $updated['translated_text']);
    }

    public function testDelete(): void
    {
        $unitId = $this->translationUnit->create("Unit for deletion", 1)['id'];
        $create = $this->translation->create($unitId, "To Delete", 2);
        $id = $create['id'];

        $delete = $this->translation->delete($id);
        $this->assertEquals("Translation has been deleted.", $delete['message']);

        $deleted = $this->translation->getById($id);
        $this->assertFalse($deleted);
    }
}
