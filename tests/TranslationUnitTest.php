<?php

use PHPUnit\Framework\TestCase;
use Robert\CAT\TranslationUnit;

class TranslationUnitTest extends TestCase
{
    private $pdoMock;
    private TranslationUnit $translationUnit;

    protected function setUp(): void
    {
        // Create a mock of the PDO class
        $this->pdoMock = $this->createMock(\PDO::class);

        // Create an instance of TranslationUnit with the mock PDO
        $this->translationUnit = new TranslationUnit($this->pdoMock);
    }

    /**
     * Test adding a new translation unit
     */
    public function testAddTranslationUnit(): void
    {
        // Create a mock for PDOStatement
        $stmtMock = $this->createMock(\PDOStatement::class);

        // Configure the mock PDO to return the mock statement when prepare is called
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO translation_units'))
            ->willReturn($stmtMock);

        // We'll create a callback to verify parameters without using at()
        $stmtMock->expects($this->exactly(3))
            ->method('bindParam')
            ->willReturnCallback(function($param, $value, $type) {
                static $callCount = 0;

                switch($callCount++) {
                    case 0:
                        $this->assertEquals(':source_content', $param);
                        $this->assertEquals('Test content', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                    case 1:
                        $this->assertEquals(':context', $param);
                        $this->assertEquals('Test context', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                    case 2:
                        $this->assertEquals(':created_by', $param);
                        $this->assertEquals(1, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                }

                return true;
            });

        $stmtMock->expects($this->once())
            ->method('execute');

        // Configure the mock PDO to return lastInsertId
        $this->pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('42');

        // Call the method under test
        $result = $this->translationUnit->addTranslationUnit('Test content', 'Test context', 1);

        // Assert the expected result
        $this->assertEquals(42, $result);
    }

    /**
     * Test retrieving a translation unit by ID
     */
    public function testGetTranslationUnitById(): void
    {
        // Create mock statements
        $unitStmtMock = $this->createMock(\PDOStatement::class);
        $translationsStmtMock = $this->createMock(\PDOStatement::class);

        // Mock data to return
        $unitData = [
            'id' => 42,
            'source_content' => 'Test content',
            'context' => 'Test context',
            'created_at' => '2023-01-01 12:00:00',
            'updated_at' => '2023-01-01 12:00:00',
            'created_by' => 1,
            'status' => 'active',
            'translations_count' => 2
        ];

        $translationsData = [
            [
                'id' => 1,
                'translation_unit_id' => 42,
                'language_id' => 2,
                'content' => 'Spanish content',
                'language_code' => 'es',
                'language_name' => 'Spanish'
            ],
            [
                'id' => 2,
                'translation_unit_id' => 42,
                'language_id' => 3,
                'content' => 'French content',
                'language_code' => 'fr',
                'language_name' => 'French'
            ]
        ];

        // Configure the mock PDO to return the mock statements
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnCallback(function($sql) use ($unitStmtMock, $translationsStmtMock) {
                if (str_contains($sql, 'SELECT tu.*')) {
                    return $unitStmtMock;
                }
                if (str_contains($sql, 'SELECT t.*, l.code as language_code')) {
                    return $translationsStmtMock;
                }
                return null;
            });

        // Configure the mock statements' expectations
        $unitStmtMock->expects($this->once())
            ->method('bindParam')
            ->with($this->equalTo(':id'), $this->equalTo(42), $this->equalTo(\PDO::PARAM_INT));

        $unitStmtMock->expects($this->once())
            ->method('execute');

        $unitStmtMock->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo(\PDO::FETCH_ASSOC))
            ->willReturn($unitData);

        $translationsStmtMock->expects($this->once())
            ->method('bindParam')
            ->with($this->equalTo(':unit_id'), $this->equalTo(42), $this->equalTo(\PDO::PARAM_INT));

        $translationsStmtMock->expects($this->once())
            ->method('execute');

        $translationsStmtMock->expects($this->once())
            ->method('fetchAll')
            ->with($this->equalTo(\PDO::FETCH_ASSOC))
            ->willReturn($translationsData);

        // Call the method under test
        $result = $this->translationUnit->getTranslationUnitById(42);

        // Assert the expected results
        $this->assertEquals(42, $result['id']);
        $this->assertEquals('Test content', $result['source_content']);
        $this->assertEquals('Test context', $result['context']);
        $this->assertEquals(2, $result['translations_count']);
        $this->assertCount(2, $result['translations']);
        $this->assertEquals('Spanish', $result['translations'][0]['language_name']);
        $this->assertEquals('French', $result['translations'][1]['language_name']);
    }

    /**
     * Test retrieving a non-existent translation unit
     */
    public function testGetNonExistentTranslationUnitById(): void
    {
        // Create a mock statement
        $stmtMock = $this->createMock(\PDOStatement::class);

        // Configure the mock PDO to return the mock statement
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Configure the mock statement's expectations
        $stmtMock->expects($this->once())
            ->method('bindParam');

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        // Call the method under test
        $result = $this->translationUnit->getTranslationUnitById(999);

        // Assert the expected result
        $this->assertNull($result);
    }

    /**
     * Test updating a translation unit
     * @throws Exception
     */
    public function testUpdateTranslationUnit(): void
    {
        // Create mock statements
        $selectStmtMock = $this->createMock(\PDOStatement::class);
        $historyStmtMock = $this->createMock(\PDOStatement::class);
        $updateStmtMock = $this->createMock(\PDOStatement::class);

        // Mock current unit data
        $currentUnitData = [
            'source_content' => 'Old content',
            'context' => 'Old context'
        ];

        // Configure the mock PDO
        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnCallback(function($sql) use ($selectStmtMock, $historyStmtMock, $updateStmtMock) {
                if (str_contains($sql, 'SELECT source_content, context')) {
                    return $selectStmtMock;
                }
                if (str_contains($sql, 'INSERT INTO translation_unit_history')) {
                    return $historyStmtMock;
                }
                if (str_contains($sql, 'UPDATE translation_units')) {
                    return $updateStmtMock;
                }
                return null;
            });

        $this->pdoMock->expects($this->once())
            ->method('beginTransaction');

        $this->pdoMock->expects($this->once())
            ->method('commit');

        // Configure the select statement
        $selectStmtMock->expects($this->once())
            ->method('bindParam')
            ->with($this->equalTo(':id'), $this->equalTo(42), $this->equalTo(\PDO::PARAM_INT));

        $selectStmtMock->expects($this->once())
            ->method('execute');

        $selectStmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn($currentUnitData);

        // Configure the history statement using willReturnCallback
        $historyStmtMock->expects($this->exactly(4))
            ->method('bindParam')
            ->willReturnCallback(function($param, $value, $type) {
                static $callCount = 0;

                switch($callCount++) {
                    case 0:
                        $this->assertEquals(':unit_id', $param);
                        $this->assertEquals(42, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                    case 1:
                        $this->assertEquals(':prev_content', $param);
                        $this->assertEquals('Old content', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                    case 2:
                        $this->assertEquals(':prev_context', $param);
                        $this->assertEquals('Old context', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                    case 3:
                        $this->assertEquals(':changed_by', $param);
                        $this->assertEquals(1, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                }

                return true;
            });

        $historyStmtMock->expects($this->once())
            ->method('execute');

        // Configure the update statement using willReturnCallback
        $updateStmtMock->expects($this->exactly(3))
            ->method('bindParam')
            ->willReturnCallback(function($param, $value, $type) {
                static $callCount = 0;

                switch($callCount++) {
                    case 0:
                        $this->assertEquals(':id', $param);
                        $this->assertEquals(42, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                    case 1:
                        $this->assertEquals(':source_content', $param);
                        $this->assertEquals('New content', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                    case 2:
                        $this->assertEquals(':context', $param);
                        $this->assertEquals('New context', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                }

                return true;
            });

        $updateStmtMock->expects($this->once())
            ->method('execute');

        // Call the method under test
        $result = $this->translationUnit->updateTranslationUnit(42, 'New content', 'New context', 1);

        // Assert the expected result
        $this->assertTrue($result);
    }

    /**
     * Test updating a non-existent translation unit
     * @throws Exception
     */
    public function testUpdateNonExistentTranslationUnit(): void
    {
        // Create a mock statement
        $stmtMock = $this->createMock(\PDOStatement::class);

        // Configure the mock PDO to return the mock statement
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Configure the mock statement's expectations
        $stmtMock->expects($this->once())
            ->method('bindParam');

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        // Call the method under test
        $result = $this->translationUnit->updateTranslationUnit(999, 'New content', 'New context', 1);

        // Assert the expected result
        $this->assertFalse($result);
    }

    /**
     * Test adding a translation
     */
    public function testAddTranslation(): void
    {
        // Create a mock statement
        $stmtMock = $this->createMock(\PDOStatement::class);

        // Configure the mock PDO
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO translations'))
            ->willReturn($stmtMock);

        $this->pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('5');

        // Configure the mock statement using willReturnCallback
        $stmtMock->expects($this->exactly(4))
            ->method('bindParam')
            ->willReturnCallback(function($param, $value, $type) {
                static $callCount = 0;

                switch($callCount++) {
                    case 0:
                        $this->assertEquals(':unit_id', $param);
                        $this->assertEquals(42, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                    case 1:
                        $this->assertEquals(':language_id', $param);
                        $this->assertEquals(2, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                    case 2:
                        $this->assertEquals(':content', $param);
                        $this->assertEquals('Spanish translation', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                    case 3:
                        $this->assertEquals(':created_by', $param);
                        $this->assertEquals(1, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                }

                return true;
            });

        $stmtMock->expects($this->once())
            ->method('execute');

        // Call the method under test
        $result = $this->translationUnit->addTranslation(42, 2, 'Spanish translation', 1);

        // Assert the expected result
        $this->assertEquals(5, $result);
    }

    /**
     * Test updating a translation
     * @throws Exception
     */
    public function testUpdateTranslation(): void
    {
        // Create mock statements
        $selectStmtMock = $this->createMock(\PDOStatement::class);
        $historyStmtMock = $this->createMock(\PDOStatement::class);
        $updateStmtMock = $this->createMock(\PDOStatement::class);

        // Mock current translation data
        $currentTranslationData = [
            'content' => 'Old translation',
            'revision_number' => 1
        ];

        // Configure the mock PDO
        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnCallback(function($sql) use ($selectStmtMock, $historyStmtMock, $updateStmtMock) {
                if (str_contains($sql, 'SELECT content, revision_number')) {
                    return $selectStmtMock;
                }
                if (str_contains($sql, 'INSERT INTO translation_history')) {
                    return $historyStmtMock;
                }
                if (str_contains($sql, 'UPDATE translations')) {
                    return $updateStmtMock;
                }
                return null;
            });

        $this->pdoMock->expects($this->once())
            ->method('beginTransaction');

        $this->pdoMock->expects($this->once())
            ->method('commit');

        // Configure the select statement
        $selectStmtMock->expects($this->once())
            ->method('bindParam')
            ->with($this->equalTo(':id'), $this->equalTo(5), $this->equalTo(\PDO::PARAM_INT));

        $selectStmtMock->expects($this->once())
            ->method('execute');

        $selectStmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn($currentTranslationData);

        // Calculate expected new revision number
        $newRevisionNumber = $currentTranslationData['revision_number'] + 1;

        // Configure the history statement using willReturnCallback
        $historyStmtMock->expects($this->exactly(4))
            ->method('bindParam')
            ->willReturnCallback(function($param, $value, $type) use ($newRevisionNumber) {
                static $callCount = 0;

                switch($callCount++) {
                    case 0:
                        $this->assertEquals(':translation_id', $param);
                        $this->assertEquals(5, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                    case 1:
                        $this->assertEquals(':prev_content', $param);
                        $this->assertEquals('Old translation', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                    case 2:
                        $this->assertEquals(':changed_by', $param);
                        $this->assertEquals(1, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                    case 3:
                        $this->assertEquals(':revision_number', $param);
                        $this->assertEquals($newRevisionNumber, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                }

                return true;
            });

        $historyStmtMock->expects($this->once())
            ->method('execute');

        // Configure the update statement using willReturnCallback
        $updateStmtMock->expects($this->exactly(3))
            ->method('bindParam')
            ->willReturnCallback(function($param, $value, $type) use ($newRevisionNumber) {
                static $callCount = 0;

                switch($callCount++) {
                    case 0:
                        $this->assertEquals(':id', $param);
                        $this->assertEquals(5, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                    case 1:
                        $this->assertEquals(':content', $param);
                        $this->assertEquals('New translation', $value);
                        $this->assertEquals(\PDO::PARAM_STR, $type);
                        break;
                    case 2:
                        $this->assertEquals(':revision_number', $param);
                        $this->assertEquals($newRevisionNumber, $value);
                        $this->assertEquals(\PDO::PARAM_INT, $type);
                        break;
                }

                return true;
            });

        $updateStmtMock->expects($this->once())
            ->method('execute');

        // Call the method under test
        $result = $this->translationUnit->updateTranslation(5, 'New translation', 1);

        // Assert the expected result
        $this->assertTrue($result);
    }

    /**
     * Test updating a non-existent translation
     * @throws Exception
     */
    public function testUpdateNonExistentTranslation(): void
    {
        // Create a mock statement
        $stmtMock = $this->createMock(\PDOStatement::class);

        // Configure the mock PDO to return the mock statement
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        // Configure the mock statement's expectations
        $stmtMock->expects($this->once())
            ->method('bindParam');

        $stmtMock->expects($this->once())
            ->method('execute');

        $stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        // Call the method under test
        $result = $this->translationUnit->updateTranslation(999, 'New translation', 1);

        // Assert the expected result
        $this->assertFalse($result);
    }
}
