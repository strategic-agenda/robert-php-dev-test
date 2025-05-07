<?php

namespace Tests\Integration\Repositories;

use App\Config\Database;
use App\Repositories\TranslationUnitRepository;
use PDO;
use Tests\TestCase;

class TranslationUnitRepositoryTest extends TestCase
{
    private $db;
    private $repository;
    
    /**
     * This test requires a working database connection
     * It's marked as skipped by default to avoid failing CI tests
     * To run this test, remove the markTestSkipped line
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->markTestSkipped('Requires a working database connection. Remove this line to run the test.');
        
        // Use a real database connection
        $this->db = new Database();
        
        // Set up test database
        $this->setupTestDatabase();
        
        $this->repository = new TranslationUnitRepository($this->db);
    }
    
    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanupTestDatabase();
        
        parent::tearDown();
    }
    
    public function testGetAll()
    {
        $units = $this->repository->getAll();
        
        // Verify we got an array of units
        $this->assertIsArray($units);
        
        // Check that our test unit was inserted and retrieved
        $found = false;
        foreach ($units as $unit) {
            if ($unit['source_text'] === 'Test source text') {
                $found = true;
                $this->assertEquals('Test target text', $unit['target_text']);
                $this->assertEquals('en', $unit['source_language']);
                $this->assertEquals('fr', $unit['target_language']);
                $this->assertIsArray($unit['history']);
                break;
            }
        }
        $this->assertTrue($found, 'Test unit was not found in the results');
    }
    
    public function testGetById()
    {
        // Find our test unit to get its ID
        $units = $this->repository->getAll();
        $testUnit = null;
        foreach ($units as $unit) {
            if ($unit['source_text'] === 'Test source text') {
                $testUnit = $unit;
                break;
            }
        }
        
        if (!$testUnit) {
            $this->fail('Test unit not found in database');
        }
        
        $unit = $this->repository->getById($testUnit['id']);
        
        $this->assertIsArray($unit);
        $this->assertEquals('Test source text', $unit['source_text']);
        $this->assertEquals('Test target text', $unit['target_text']);
        $this->assertEquals('en', $unit['source_language']);
        $this->assertEquals('fr', $unit['target_language']);
        $this->assertIsArray($unit['history']);
    }
    
    public function testCreate()
    {
        $data = [
            'source_language' => 'en',
            'target_language' => 'es',
            'source_text' => 'New test source text',
            'target_text' => 'Nueva prueba de texto'
        ];
        
        $result = $this->repository->create($data);
        
        $this->assertIsArray($result);
        $this->assertEquals('New test source text', $result['source_text']);
        $this->assertEquals('Nueva prueba de texto', $result['target_text']);
        $this->assertEquals('en', $result['source_language']);
        $this->assertEquals('es', $result['target_language']);
        $this->assertIsArray($result['history']);
        
        // Verify it's in the database
        $stmt = $this->db->prepare('SELECT * FROM translation_units WHERE id = :id');
        $stmt->execute(['id' => $result['id']]);
        $dbResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals('New test source text', $dbResult['source_text']);
        $this->assertEquals('Nueva prueba de texto', $dbResult['target_text']);
    }
    
    public function testUpdate()
    {
        // Find our test unit to get its ID
        $units = $this->repository->getAll();
        $testUnit = null;
        foreach ($units as $unit) {
            if ($unit['source_text'] === 'Test source text') {
                $testUnit = $unit;
                break;
            }
        }
        
        if (!$testUnit) {
            $this->fail('Test unit not found in database');
        }
        
        // Update the unit
        $data = ['target_text' => 'Updated test target text'];
        $result = $this->repository->update($testUnit['id'], $data);
        
        $this->assertTrue($result);
        
        // Verify the update in the database
        $updated = $this->repository->getById($testUnit['id']);
        $this->assertEquals('Updated test target text', $updated['target_text']);
        
        // Verify history was created
        $this->assertNotEmpty($updated['history']);
        $this->assertEquals('Test target text', $updated['history'][0]['target_text']);
    }
    
    public function testDelete()
    {
        // Find our test unit to get its ID
        $units = $this->repository->getAll();
        $testUnit = null;
        foreach ($units as $unit) {
            if ($unit['source_text'] === 'Test source text') {
                $testUnit = $unit;
                break;
            }
        }
        
        if (!$testUnit) {
            $this->fail('Test unit not found in database');
        }
        
        // Delete the unit
        $result = $this->repository->delete($testUnit['id']);
        
        $this->assertTrue($result);
        
        // Verify it's gone
        $deleted = $this->repository->getById($testUnit['id']);
        $this->assertNull($deleted);
        
        // Verify history is gone too
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM translation_history WHERE translation_unit_id = :id');
        $stmt->execute(['id' => $testUnit['id']]);
        $count = $stmt->fetchColumn();
        $this->assertEquals(0, $count);
    }
    
    public function testGetHistoryForUnit()
    {
        // Find our test unit to get its ID
        $units = $this->repository->getAll();
        $testUnit = null;
        foreach ($units as $unit) {
            if ($unit['source_text'] === 'Test source text') {
                $testUnit = $unit;
                break;
            }
        }
        
        if (!$testUnit) {
            $this->fail('Test unit not found in database');
        }
        
        // Add some history
        $this->repository->addHistoryEntry($testUnit['id'], 'Old translation 1');
        $this->repository->addHistoryEntry($testUnit['id'], 'Old translation 2');
        
        // Get history
        $history = $this->repository->getHistoryForUnit($testUnit['id']);
        
        $this->assertIsArray($history);
        $this->assertCount(2, $history);
        $this->assertEquals('Old translation 2', $history[0]['target_text']);
        $this->assertEquals('Old translation 1', $history[1]['target_text']);
    }
    
    private function setupTestDatabase()
    {
        // Insert test unit
        $stmt = $this->db->prepare('
            INSERT INTO translation_units 
            (source_language, target_language, source_text, target_text, created_at, updated_at)
            VALUES (:source_language, :target_language, :source_text, :target_text, :created_at, :updated_at)
        ');
        
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            'source_language' => 'en',
            'target_language' => 'fr',
            'source_text' => 'Test source text',
            'target_text' => 'Test target text',
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }
    
    private function cleanupTestDatabase()
    {
        // Get all test units
        $stmt = $this->db->prepare("
            SELECT id FROM translation_units 
            WHERE source_text LIKE 'Test%' OR source_text LIKE 'New test%'
        ");
        $stmt->execute();
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Delete history for these units
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("DELETE FROM translation_history WHERE translation_unit_id IN ($placeholders)");
            $stmt->execute($ids);
            
            // Delete the units
            $stmt = $this->db->prepare("DELETE FROM translation_units WHERE id IN ($placeholders)");
            $stmt->execute($ids);
        }
    }
} 