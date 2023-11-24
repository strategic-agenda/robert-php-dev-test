<?php

use Lampdev\RobertPhpDevTest\TranslationUnit;
use PHPUnit\Framework\TestCase;

class TranslationUnitTest extends TestCase
{
    private $db;
    private $translationUnit;

    public function testGetAllTranslationUnits()
    {
        // Sample data to be returned
        $sampleData = [
            ['id' => 1, 'source_text' => 'Hello', 'language_id' => 1],
            ['id' => 2, 'source_text' => 'Bonjour', 'language_id' => 2]
        ];

        // Create a mock PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetchAll')->willReturn($sampleData);

        // Set up the expectation for the query method to be called once and to return the mock PDOStatement
        $this->db->expects($this->once())
            ->method('query')
            ->with($this->equalTo("SELECT * FROM translation_units"))
            ->willReturn($stmtMock);

        // Call the method
        $result = $this->translationUnit->getAllTranslationUnits();

        // Assertions
        $this->assertIsArray($result);
        $this->assertEquals($sampleData, $result);
    }

    public function testAddTranslationUnit()
    {
        // Define the input
        $text = 'Sample Text';
        $languageId = 1;
        $expectedLastInsertId = 1;  // Example last insert ID

        // Create a mock PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        // Set up the expectation for the prepare method to be called and return the mock PDOStatement
        $this->db->expects($this->atLeastOnce())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Mock the lastInsertId method to return the expected last insert ID
        $this->db->method('lastInsertId')->willReturn(
            (string)$expectedLastInsertId
        );

        // Call the method
        $this->translationUnit->addTranslationUnit($text, $languageId);

        // Since we're not checking the result, just assert that the method completes successfully
        $this->assertTrue(true);
    }

    public function testUpdateTranslationUnit()
    {
        // Define input parameters
        $id = 1;
        $newText = 'Updated Text';

        // Create mock PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        // Set up the expectation for the prepare method to be called twice and return the mock PDOStatement
        $this->db->expects($this->exactly(4))
            ->method('prepare')
            ->willReturn($stmtMock);

        // Mock transaction methods
        $this->db->expects($this->once())->method('beginTransaction');
        $this->db->expects($this->once())->method('commit');

        // Call the method
        $this->translationUnit->updateTranslationUnit($id, $newText);

        $this->assertTrue(true);
    }

    public function testGetTranslationUnit()
    {
        // Define the unit ID to fetch and the expected result
        $unitId = 1;
        $expectedResult = [
            'id' => $unitId,
            'source_text' => 'Example Text',
            'language_id' => 2
        ];

        // Create a mock PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->with($this->equalTo([$unitId]));
        $stmtMock->method('fetch')
            ->willReturn($expectedResult);

        // Set up the expectation for the prepare method to be called once and return the mock PDOStatement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Call the method
        $result = $this->translationUnit->getTranslationUnit($unitId);

        // Assertions
        $this->assertIsArray($result);
        $this->assertEquals($expectedResult, $result);
    }

    public function testDeleteTranslationUnit()
    {
        // Define the unit ID to be deleted
        $unitId = 1;

        // Create a mock PDOStatement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects(
            $this->exactly(2)
        ) // Assuming two execute calls: one for DELETE FROM translation_versions, one for DELETE FROM translation_units
        ->method('execute')
            ->with($this->equalTo([$unitId]));

        // Set up the expectation for the prepare method to be called twice and return the mock PDOStatement
        $this->db->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($stmtMock);

        // Mock transaction methods
        $this->db->expects($this->once())->method('beginTransaction');
        $this->db->expects($this->once())->method('commit');
        $this->db->expects($this->never())->method('rollBack');

        // Call the method
        $this->translationUnit->deleteTranslationUnit($unitId);
        // No assertions are necessary for a void method, but we check that all expectations are met
    }

    protected function setUp(): void
    {
        // Mock the PDO object and pass it to the TranslationUnit constructor
        $this->db = $this->createMock(PDO::class);
        $this->translationUnit = new TranslationUnit($this->db);
    }
}
