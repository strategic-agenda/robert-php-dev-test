<?php

namespace Tests\Integration\Database;

use App\Config\Database;
use PDO;
use Tests\TestCase;

class ConnectionTest extends TestCase
{
    /**
     * Test that we can connect to the database
     * 
     * Note: This test requires a working database connection
     * It's marked as skipped by default to avoid failing CI tests
     * To run this test, remove the markTestSkipped line
     */
    public function testDatabaseConnection()
    {
        $this->markTestSkipped('Requires a working database connection. Remove this line to run the test.');
        
        $db = new Database();
        $this->assertInstanceOf(PDO::class, $db);
        
        // Test that we can execute a simple query
        $stmt = $db->query('SELECT 1');
        $this->assertInstanceOf(\PDOStatement::class, $stmt);
        
        $result = $stmt->fetchColumn();
        $this->assertEquals(1, $result);
    }
} 