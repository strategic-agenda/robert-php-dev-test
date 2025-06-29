<?php

use PHPUnit\Framework\TestCase;
use CAT\Service\TranslationService;
use CAT\Repository\TranslationUnitRepository;
use CAT\Repository\TranslationRepository;
use CAT\Database;

class TranslationServiceTest extends TestCase
{
    private $db;
    private $unitRepo;
    private $translationRepo;
    private $service;

    protected function setUp(): void
    {
        // uses the docker postgres
        $this->db = new CAT\Database('cat_postgres_test', 'catdb_test', 'catuser', 'catpass', 5432);
        $this->db->initializeSchema();
        $this->unitRepo = new TranslationUnitRepository($this->db);
        $this->translationRepo = new TranslationRepository($this->db);
        $this->service = new TranslationService($this->unitRepo, $this->translationRepo);
        $pdo = $this->db->getConnection();
        $pdo->exec('DELETE FROM translations');
        $pdo->exec('DELETE FROM translation_units');
    }

    public function testCreateAndGetTranslationUnit()
    {
        $unit = $this->service->createTranslationUnit('boo', 1);
        $this->assertEquals('boo', $unit['source_text']);
        $this->assertEquals(1, $unit['source_language_id']);
    }

    public function testUpdateTranslationUnit()
    {
        $unit = $this->service->createTranslationUnit('test', 1);
        $this->assertEquals('test', $unit['source_text']);
        $updated = $this->service->updateTranslationUnit($unit['id'], 'test2');
        $this->assertEquals('test2', $updated['source_text']);
    }

    public function testDeleteTranslationUnit()
    {
        $unit = $this->service->createTranslationUnit('deleted', 1);
        $deleted = $this->service->deleteTranslationUnit($unit['id']);
        $this->assertTrue($deleted);
        $fetched = $this->service->getTranslationUnit($unit['id']);
        $this->assertNull($fetched);
    }

    public function testListTranslationUnits()
    {
        $this->service->createTranslationUnit('this is a teest', 1);
        $this->service->createTranslationUnit('hello world', 1);
        $list = $this->service->listTranslationUnits();
        $this->assertGreaterThanOrEqual(2, count($list));
        $this->assertEquals('this is a teest', $list[0]['source_text']);
    }
} 