<?php

namespace Tests\Integration\Repositories;

use App\Config\Database;
use App\Repositories\LanguageRepository;
use PDO;
use Tests\TestCase;

class LanguageRepositoryTest extends TestCase
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
        
        // Set up test database - ideally, this would be in a separate test database
        $this->setupTestDatabase();
        
        $this->repository = new LanguageRepository($this->db);
    }
    
    protected function tearDown(): void
    {
        // Clean up test data
        $this->cleanupTestDatabase();
        
        parent::tearDown();
    }
    
    public function testGetAll()
    {
        $languages = $this->repository->getAll();
        
        // Verify we got an array of languages
        $this->assertIsArray($languages);
        
        // Check that our test language was inserted and retrieved
        $found = false;
        foreach ($languages as $language) {
            if ($language['code'] === 'zz') {
                $found = true;
                $this->assertEquals('Test Language', $language['name']);
                break;
            }
        }
        $this->assertTrue($found, 'Test language was not found in the results');
    }
    
    public function testGetByCode()
    {
        $language = $this->repository->getByCode('zz');
        
        $this->assertIsArray($language);
        $this->assertEquals('zz', $language['code']);
        $this->assertEquals('Test Language', $language['name']);
    }
    
    public function testCreate()
    {
        $data = [
            'code' => 'xx',
            'name' => 'New Test Language'
        ];
        
        $result = $this->repository->create($data);
        
        $this->assertIsArray($result);
        $this->assertEquals('xx', $result['code']);
        $this->assertEquals('New Test Language', $result['name']);
        
        // Verify it's in the database
        $stmt = $this->db->prepare('SELECT * FROM languages WHERE code = :code');
        $stmt->execute(['code' => 'xx']);
        $dbResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEquals('xx', $dbResult['code']);
        $this->assertEquals('New Test Language', $dbResult['name']);
    }
    
    public function testUpdate()
    {
        // First, find our test language to get its ID
        $language = $this->repository->getByCode('zz');
        $id = $language['id'];
        
        // Update the language
        $data = ['name' => 'Updated Test Language'];
        $result = $this->repository->update($id, $data);
        
        $this->assertTrue($result);
        
        // Verify the update in the database
        $updated = $this->repository->getById($id);
        $this->assertEquals('Updated Test Language', $updated['name']);
    }
    
    public function testDelete()
    {
        // First, find our test language to get its ID
        $language = $this->repository->getByCode('zz');
        $id = $language['id'];
        
        // Delete the language
        $result = $this->repository->delete($id);
        
        $this->assertTrue($result);
        
        // Verify it's gone
        $deleted = $this->repository->getById($id);
        $this->assertNull($deleted);
    }
    
    private function setupTestDatabase()
    {
        // Insert test language
        $stmt = $this->db->prepare('
            INSERT INTO languages (code, name, direction, active, created_at)
            VALUES (:code, :name, :direction, :active, :created_at)
            ON DUPLICATE KEY UPDATE name = :name
        ');
        
        $stmt->execute([
            'code' => 'zz',
            'name' => 'Test Language',
            'direction' => 'ltr',
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    private function cleanupTestDatabase()
    {
        // Delete test data
        $this->db->exec("DELETE FROM languages WHERE code IN ('zz', 'xx')");
    }
} 