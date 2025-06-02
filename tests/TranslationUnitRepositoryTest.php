<?php

require_once 'vendor/autoload.php';

use App\Repository\TranslationUnitRepository;
use PHPUnit\Framework\TestCase;
class TranslationUnitRepositoryTest extends TestCase
{
    private TranslationUnitRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new TranslationUnitRepository();
    }

    public function testAddUnitInsertsDataCorrectly()
    {
        $sourceText = "Hola mundo test";
        $sourceLangId = 1;

        $id = $this->repo->addUnit($sourceText, $sourceLangId);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $unit = $this->repo->getUnitById($id);
        $this->assertEquals($sourceText, $unit['source_text']);
        $this->assertEquals($sourceLangId, $unit['source_lang_id']);
    }

    public function testAddUnitWithEmptyTextThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->repo->addUnit("   ", 1);
    }
 
}