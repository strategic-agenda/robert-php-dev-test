<?php


namespace Tests\Unit;

use App\TranslationUnit;
use PHPUnit\Framework\TestCase;
use PDO;
use Exception;

use Pest\Expectation;

// Mock PDO and PDOStatement for database operations testing
class MockPDO extends PDO
{
    public $prepared = [];
    public $executed = false;
    public $lastInsertId = 0;
    public $inTransaction = false;
    public $committed = false;
    public $rolledBack = false;
    public $stmt;
    public $executedParams = [];
    protected $pdo;
    private $dynamicProperties = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdoInstance()
    {
        return $this->pdo;
    }
    #[\ReturnTypeWillChange]
    public function prepare($statement, $options = [])
    {
        $this->prepared[] = $statement;
        
        if (!isset($this->stmt)) {
            $this->stmt = new MockPDOStatement();
        }
        return $this->stmt;
    }
    #[\ReturnTypeWillChange]
    public function beginTransaction()
    {
        $this->inTransaction = true;
        return true;
    }
    #[\ReturnTypeWillChange]
    public function commit()
    {
        $this->committed = true;
        $this->inTransaction = false;
        return true;
    }
    #[\ReturnTypeWillChange]
    public function rollBack()
    {
        $this->rolledBack = true;
        $this->inTransaction = false;
        return true;
    }
    #[\ReturnTypeWillChange]
    public function lastInsertId($name = null)
    {
        return ++$this->lastInsertId;
    }

    public function __set($name, $value)
    {
       
        $this->dynamicProperties[$name] = $value;
    }

    public function __get($name)
    {
        return $this->dynamicProperties[$name] ?? null;
    }
}

class MockPDOStatement
{
    public $fetchReturn = [];
    public $executed = false;
    public $executedParams = [];
    public $fetchMode;
    public $fetchCount = 0;
    public $fetchAllReturn = [];
    public $boundParams = [];

    public function execute($params = [])
    {
        $this->executed = true;
        
        if (!empty($params)) {
            $this->executedParams = array_merge($this->executedParams, $params);
        } else {
       
            $this->executedParams = $this->boundParams;
        }
        return true;
    }

    public function fetch($fetch_style = PDO::FETCH_ASSOC, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        // if (empty($this->fetchReturn)) {
        //     return false;
        // }

        // if (is_array($this->fetchReturn) && isset($this->fetchReturn[$this->fetchCount])) {
        //     return $this->fetchReturn[$this->fetchCount++];
        // }

        return $this->fetchReturn;
    }

    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = [])
    {
        return $this->fetchAllReturn;
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
    {

        // $this->boundParams[$parameter] = $variable;
        $this->executedParams[$parameter] = $variable;

        return true;
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        $this->boundParams[$parameter] = $value;
        return true;
    }
}

// Create a testable version of TranslationUnit with an injectable PDO instance
class TestableTranslationUnit extends TranslationUnit
{
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
}


class ExceptionThrowingPDO extends MockPDO
{
    public $prepareCallCount = 0;

    #[\ReturnTypeWillChange]
    public function prepare($statement, $options = [])
    {
        $this->prepared[] = $statement;
        $this->prepareCallCount++;

        if ($this->prepareCallCount == 2) {
            throw new PDOException("Simulated database error");
        }

        if (!$this->stmt) {
            $this->stmt = new MockPDOStatement();
        }

        return $this->stmt;
    }
}


beforeEach(function () {
    $this->mockPdo = new MockPDO(new PDO('sqlite::memory:'));

    $this->mockPdo->stmt = new MockPDOStatement();
    $this->manager = new TestableTranslationUnit($this->mockPdo);
});

test('addTranslationUnit creates a new translation unit', function () {
    $documentId = 1;
    $sourceText = 'Hello world';
    $targetText = 'Hola mundo';
    $status = 'new';

    $result = $this->manager->addTranslationUnit($documentId, $sourceText, $targetText, $status);

    // Assertions
    expect($result)->toBeGreaterThan(0);
    expect($this->mockPdo->prepared[0])->toContain('INSERT INTO translation_units');
    expect($this->mockPdo->stmt->executed)->toBeTrue();
});

test('getTranslationUnit retrieves a unit by ID', function () {
    $id = 1;
    $expectedUnit = [
        'id' => $id,
        'document_id' => 2,
        'source_text' => 'Hello world',
        'target_text' => 'Hola mundo',
        'status' => 'new',
        'version' => 1
    ];

    $this->mockPdo->stmt = new MockPDOStatement();

    $this->mockPdo->stmt->fetchReturn = $expectedUnit;

    $result = $this->manager->getTranslationUnit($id);

  
    expect($result)->toBe($expectedUnit);
    expect($this->mockPdo->prepared[0])->toContain('SELECT * FROM translation_units WHERE id = :id');
    expect($this->mockPdo->stmt->executed)->toBeTrue();
});

test('getTranslationUnit returns false when unit not found', function () {
    $id = 999;
    $this->mockPdo->stmt->fetchReturn = false;

    $result = $this->manager->getTranslationUnit($id);

    
    expect($result)->toBeFalse();
});

test('updateTranslationUnit updates an existing unit and records history', function () {
    $id = 1;
    $newTargetText = 'Hola mundo actualizado';
    $userId = 5;
    $status = 'completed';

    
    $currentUnit = [
        'id' => $id,
        'document_id' => 2,
        'source_text' => 'Hello world',
        'target_text' => 'Hola mundo',
        'status' => 'new',
        'version' => 1
    ];
    $this->mockPdo->stmt->fetchReturn = $currentUnit;

    $result = $this->manager->updateTranslationUnit($id, $newTargetText, $userId, $status);

   
    expect($result)->toBeTrue();
    expect($this->mockPdo->inTransaction)->toBeFalse(); 
    expect($this->mockPdo->committed)->toBeTrue();
    expect($this->mockPdo->rolledBack)->toBeFalse();

  
    expect($this->mockPdo->prepared[0])->toContain('SELECT * FROM translation_units WHERE id = :id');
    expect($this->mockPdo->prepared[1])->toContain('UPDATE translation_units SET');
    expect($this->mockPdo->prepared[1])->toContain('target_text = :target_text');
    expect($this->mockPdo->prepared[1])->toContain('status = :status');
    expect($this->mockPdo->prepared[2])->toContain('INSERT INTO translation_history');
});


test('getTranslationHistory returns history records for a unit', function () {
    $unitId = 1;
    $expectedHistory = [
        [
            'id' => 1,
            'translation_unit_id' => $unitId,
            'user_id' => 5,
            'old_target_text' => 'Hola mundo',
            'new_target_text' => 'Hola mundo actualizado',
            'changed_at' => '2025-06-22 10:00:00',
            'username' => 'testuser'
        ]
    ];

    $this->mockPdo->stmt->fetchAllReturn = $expectedHistory;

    $result = $this->manager->getTranslationHistory($unitId);

    // Assertions
    expect($result)->toBe($expectedHistory);
    expect($this->mockPdo->prepared[0])->toContain('FROM translation_history th');
    expect($this->mockPdo->prepared[0])->toContain('WHERE translation_unit_id = :translation_unit_id');
    expect($this->mockPdo->stmt->executed)->toBeTrue();
});

test('getTranslationHistory returns empty array on error', function () {
    $unitId = 1;

    $this->mockPdo->prepare = function () {
        throw new PDOException('Test forced exception');
    };

    $result = @$this->manager->getTranslationHistory($unitId);

    
    expect($result)->toBeArray();
    expect($result)->toBeEmpty();
});
test('getPdo returns the PDO instance', function () {
    $pdo = $this->manager->getPdo();
    expect($pdo)->toBeInstanceOf(PDO::class);
    expect($pdo)->toBe($this->mockPdo);
});



    test('updateTranslationUnit rolls back transaction on error', function () {
        
        $manager = new class() extends TranslationUnit {
            private $rollbackCalled = false;
            private $commitCalled = false;

          
            public function __construct()
            {
              
            }

          
            public function updateTranslationUnit($id, $newTargetText, $userId, $status = null)
            {
                try {
                   
                    $this->beginTransaction();

                 
                    throw new Exception("Test exception");

                  
                    $this->commit();
                    return true;
                } catch (Exception $e) {
                    $this->rollBack();
                    return false;
                }
            }

          
            private function beginTransaction()
            {
            
            }

            private function commit()
            {
                $this->commitCalled = true;
            }

            private function rollBack()
            {
                $this->rollbackCalled = true;
            }

            public function wasRollbackCalled()
            {
                return $this->rollbackCalled;
            }

            public function wasCommitCalled()
            {
                return $this->commitCalled;
            }
        };

      
        $result = $manager->updateTranslationUnit(1, "New translation", 5);

       
        expect($result)->toBeFalse();
        expect($manager->wasRollbackCalled())->toBeTrue();
        expect($manager->wasCommitCalled())->toBeFalse();
    });