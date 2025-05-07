<?php

use PHPUnit\Framework\TestCase;

class TranslationUnitTest extends TestCase
{
    /**
     * Static PDO instance shared across all test methods
     *
     * @var PDO
     */
    private static PDO $pdo;

    /**
     * @var TranslationUnit
     */
    private TranslationUnit $translationUnit;
    
    /**
     * Set up once before any tests run
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        // create the database connection once before running any tests
        self::$pdo = new PDO('mysql:host=localhost;dbname=robert_dev_test', 'root', 'root');
    }
    
    /**
     * Set up before each test
     */
    protected function setUp(): void
    {
        parent::setUp();

        // use the existing connection
        $this->translationUnit = new TranslationUnit(self::$pdo);
    }
    
    /**
     * Test adding a new translation unit
     */
    public function testAddTranslationUnit(): void
    {
        // test adding a unit with no translations
        $unitId1 = $this->translationUnit->add('Hello world');
        $this->assertIsInt($unitId1);
        $this->assertGreaterThan(0, $unitId1);
        
        // test adding a unit with translations
        $translations = ['es' => 'Hola mundo', 'fr' => 'Bonjour le monde'];
        $unitId2 = $this->translationUnit->add('Hello world', $translations);
        $this->assertIsInt($unitId2);
        $this->assertGreaterThan(0, $unitId2);
        
        // test that retrieved unit matches what was added
        $unit = $this->translationUnit->get($unitId2);
        $this->assertEquals('Hello world', $unit['source_text']);
        $this->assertEquals($translations, $unit['translations']);
    }
    
    /**
     * Test getting a translation unit by ID
     */
    public function testGetTranslationUnit(): void
    {
        // add a test unit
        $translations = ['es' => 'Texto de prueba'];
        $unitId = $this->translationUnit->add('Test text', $translations);
        
        // get the unit and validate its properties
        $unit = $this->translationUnit->get($unitId);
        
        $this->assertIsArray($unit);
        $this->assertEquals($unitId, $unit['id']);
        $this->assertEquals('Test text', $unit['source_text']);
        $this->assertEquals($translations, $unit['translations']);
        $this->assertArrayHasKey('created_at', $unit);
        $this->assertArrayHasKey('updated_at', $unit);
    }
    
    /**
     * Test that getting a non-existent unit returns null
     */
    public function testGetNonExistentUnitReturnsNull(): void
    {
        $unit = $this->translationUnit->get(99999);
        $this->assertNull($unit);
    }
    
    /**
     * Test updating a translation unit's source text
     */
    public function testUpdateSourceText(): void
    {
        // add a test unit
        $unitId = $this->translationUnit->add('Original text');
        
        // update the source text
        $result = $this->translationUnit->update($unitId, 'Updated text');
        $this->assertTrue($result);
        
        // verify the update was successful
        $unit = $this->translationUnit->get($unitId);
        $this->assertEquals('Updated text', $unit['source_text']);
    }

    /**
     * Test updating a translation unit's translations
     */
    public function testUpdateTranslations(): void
    {
        // add a test unit with initial translations
        $initialTranslations = ['es' => 'Texto inicial'];
        $unitId = $this->translationUnit->add('Test text', $initialTranslations);

        // update translations
        $newTranslations = [
            'es' => 'Texto actualizado',
            'fr' => 'Texte mis à jour'
        ];
        $result = $this->translationUnit->update($unitId, null, $newTranslations);
        $this->assertTrue($result);

        // verify the update was successful
        $unit = $this->translationUnit->get($unitId);
        $this->assertEquals($newTranslations, $unit['translations']);
    }
    
    /**
     * Test updating both source text and translations simultaneously
     */
    public function testUpdateSourceTextAndTranslations(): void
    {
        // add a test unit
        $unitId = $this->translationUnit->add('Original text', ['es' => 'Texto original']);
        
        // update both source text and translations
        $newText = 'New text';
        $newTranslations = ['es' => 'Texto nuevo', 'de' => 'Neuer Text'];
        $result = $this->translationUnit->update($unitId, $newText, $newTranslations);
        $this->assertTrue($result);
        
        // verify all updates were successful
        $unit = $this->translationUnit->get($unitId);
        $this->assertEquals($newText, $unit['source_text']);
        $this->assertEquals($newTranslations, $unit['translations']);
    }
    
    /**
     * Test that updating a non-existent unit fails
     */
    public function testUpdateNonExistentUnitFails(): void
    {
        $result = $this->translationUnit->update(99999, 'Some text');
        $this->assertFalse($result);
    }
    
    /**
     * Test that the updated_at timestamp changes after an update
     */
    public function testUpdateChangesTimestamp(): void
    {
        // add a unit
        $unitId = $this->translationUnit->add('Original text');
        $originalUnit = $this->translationUnit->get($unitId);
        
        // wait 1 second to ensure timestamp will be different
        sleep(1);
        
        // update the unit
        $this->translationUnit->update($unitId, 'Updated text');

        $updatedUnit = $this->translationUnit->get($unitId);
        
        // verify timestamps are different
        $this->assertNotEquals($originalUnit['updated_at'], $updatedUnit['updated_at']);
    }
    
    /**
     * Test getting the history of a translation unit
     */
    public function testGetHistory(): void
    {
        // add a test unit with initial translations
        $unitId = $this->translationUnit->add('Test text', ['es' => 'Versión inicial']);
        
        // make several updates to translations to create history
        $this->translationUnit->update($unitId, null, ['es' => 'Segunda versión']);
        $this->translationUnit->update($unitId, null, ['es' => 'Tercera versión']);
        
        // get the history and verify it contains the expected versions
        $history = $this->translationUnit->getHistory($unitId);
        
        $this->assertIsArray($history);
        $this->assertArrayHasKey('es', $history);
        $this->assertCount(2, $history['es']); // Should have 2 historical versions
        
        // check structure of first history item
        $this->assertArrayHasKey('text', $history['es'][0]);
        $this->assertArrayHasKey('version', $history['es'][0]);
        $this->assertArrayHasKey('created_at', $history['es'][0]);
        
        // verify the versions are as expected (newest version should be first due to DESC ordering)
        $this->assertEquals('Segunda versión', $history['es'][0]['text']);
        $this->assertEquals('Versión inicial', $history['es'][1]['text']);
    }
    
    /**
     * Test that history for a non-existent unit returns an empty array
     */
    public function testGetHistoryForNonExistentUnit(): void
    {
        $history = $this->translationUnit->getHistory(99999);
        $this->assertIsArray($history);
        $this->assertEmpty($history);
    }
    
    /**
     * Test getting all translation units
     */
    public function testGetAll(): void
    {
        // Clear existing units
        $this->clearTranslationUnits();
        
        // add multiple units
        $unitId1 = $this->translationUnit->add('First unit');
        $unitId2 = $this->translationUnit->add('Second unit');
        $unitId3 = $this->translationUnit->add('Third unit');
        
        // get all units
        $allUnits = $this->translationUnit->getAll();
        
        // verify all units are returned
        $this->assertCount(3, $allUnits);
        
        // build array of IDs from returned units
        $returnedIds = array_map(function($unit) {
            return $unit['id'];
        }, $allUnits);
        
        // verify our units are in the results
        $this->assertContains($unitId1, $returnedIds);
        $this->assertContains($unitId2, $returnedIds);
        $this->assertContains($unitId3, $returnedIds);
    }
    
    /**
     * Helper method to clear all translation units for clean testing
     */
    private function clearTranslationUnits(): void
    {
        self::$pdo->exec('DELETE FROM translations');
        self::$pdo->exec('DELETE FROM translation_versions');
        self::$pdo->exec('DELETE FROM translation_units');
    }
    
    /**
     * Test that getAll returns an empty array when no units exist
     */
    public function testGetAllWithNoUnits(): void
    {
        // clear existing units
        $this->clearTranslationUnits();
        
        $allUnits = $this->translationUnit->getAll();
        $this->assertIsArray($allUnits);
        $this->assertEmpty($allUnits);
    }
    
    /**
     * Clean up after all tests have run
     */
    public static function tearDownAfterClass(): void
    {
        // close the database connection
        self::$pdo = null;
        
        parent::tearDownAfterClass();
    }
}
