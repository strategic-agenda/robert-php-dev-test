<?php

namespace Tests\Unit\Services;

use App\Interfaces\TranslationUnitRepositoryInterface;
use App\Services\TranslationUnitService;
use InvalidArgumentException;
use Tests\TestCase;

class TranslationUnitServiceTest extends TestCase
{
    private $repository;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->mock(TranslationUnitRepositoryInterface::class);
        $this->service = new TranslationUnitService($this->repository);
    }

    public function testGetAll()
    {
        $expectedUnits = [
            [
                'id' => 1, 
                'source_language' => 'en', 
                'target_language' => 'fr',
                'source_text' => 'Hello',
                'target_text' => 'Bonjour',
                'history' => []
            ],
            [
                'id' => 2, 
                'source_language' => 'en', 
                'target_language' => 'es',
                'source_text' => 'Hello',
                'target_text' => 'Hola',
                'history' => []
            ]
        ];

        $this->repository->expects($this->once())
            ->method('getAll')
            ->willReturn($expectedUnits);

        $result = $this->service->getAll();
        $this->assertEquals($expectedUnits, $result);
    }

    public function testGetReturnsUnit()
    {
        $unitId = 1;
        $expectedUnit = [
            'id' => 1, 
            'source_language' => 'en', 
            'target_language' => 'fr',
            'source_text' => 'Hello',
            'target_text' => 'Bonjour',
            'history' => []
        ];

        $this->repository->expects($this->once())
            ->method('getById')
            ->with($unitId)
            ->willReturn($expectedUnit);

        $result = $this->service->get($unitId);
        $this->assertEquals($expectedUnit, $result);
    }

    public function testGetReturnsNullWhenUnitNotFound()
    {
        $unitId = 999;

        $this->repository->expects($this->once())
            ->method('getById')
            ->with($unitId)
            ->willReturn(null);

        $result = $this->service->get($unitId);
        $this->assertNull($result);
    }

    public function testCreateUnit()
    {
        $unitData = [
            'sourceLanguage' => 'en',
            'targetLanguage' => 'fr',
            'sourceText' => 'Hello',
            'targetText' => 'Bonjour'
        ];
        
        $dbData = [
            'source_language' => 'en',
            'target_language' => 'fr',
            'source_text' => 'Hello',
            'target_text' => 'Bonjour'
        ];
        
        $repositoryResult = array_merge(['id' => 1], $dbData, ['history' => []]);

        $this->repository->expects($this->once())
            ->method('create')
            ->with($dbData)
            ->willReturn($repositoryResult);

        $result = $this->service->create($unitData);
        
        // Verify that basic properties match
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('en', $result['source_language']);
        $this->assertEquals('fr', $result['target_language']);
        $this->assertEquals('Hello', $result['source_text']);
        $this->assertEquals('Bonjour', $result['target_text']);
        $this->assertEquals([], $result['history']);
        
        // Verify the camelCase properties are also present
        $this->assertEquals('en', $result['sourceLanguage']);
        $this->assertEquals('fr', $result['targetLanguage']);
        $this->assertEquals('Hello', $result['sourceText']);
        $this->assertEquals('Bonjour', $result['targetText']);
    }

    public function testCreateUnitWithMissingData()
    {
        $unitData = [
            'sourceLanguage' => 'en',
            // Missing targetLanguage
            'sourceText' => 'Hello',
            'targetText' => 'Bonjour'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->service->create($unitData);
    }

    public function testUpdateTranslation()
    {
        $unitId = 1;
        $targetText = 'Nouveau bonjour';
        
        $updatedUnit = [
            'id' => 1, 
            'source_language' => 'en', 
            'target_language' => 'fr',
            'source_text' => 'Hello',
            'target_text' => $targetText,
            'history' => [
                [
                    'id' => 1,
                    'translation_unit_id' => 1,
                    'target_text' => 'Bonjour',
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];

        $this->repository->expects($this->once())
            ->method('update')
            ->with($unitId, ['target_text' => $targetText])
            ->willReturn(true);
            
        $this->repository->expects($this->once())
            ->method('getById')
            ->with($unitId)
            ->willReturn($updatedUnit);

        $result = $this->service->updateTranslation($unitId, $targetText);
        
        // Verify basic properties
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('en', $result['source_language']);
        $this->assertEquals('fr', $result['target_language']);
        $this->assertEquals('Hello', $result['source_text']);
        $this->assertEquals($targetText, $result['target_text']);
        $this->assertCount(1, $result['history']);
        
        // Verify camelCase properties
        $this->assertEquals('en', $result['sourceLanguage']);
        $this->assertEquals('fr', $result['targetLanguage']);
        $this->assertEquals('Hello', $result['sourceText']);
        $this->assertEquals($targetText, $result['targetText']);
    }

    public function testDeleteUnit()
    {
        $unitId = 1;

        $this->repository->expects($this->once())
            ->method('delete')
            ->with($unitId)
            ->willReturn(true);

        $result = $this->service->delete($unitId);
        $this->assertTrue($result);
    }

    public function testGetHistory()
    {
        $unitId = 1;
        $history = [
            [
                'id' => 1,
                'translation_unit_id' => 1,
                'target_text' => 'Bonjour',
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->repository->expects($this->once())
            ->method('getHistoryForUnit')
            ->with($unitId)
            ->willReturn($history);

        $result = $this->service->getHistory($unitId);
        $this->assertEquals($history, $result);
    }

    public function testValidateUnitDataWithValidData()
    {
        $data = [
            'sourceLanguage' => 'en',
            'targetLanguage' => 'fr',
            'sourceText' => 'Hello',
            'targetText' => 'Bonjour'
        ];

        $result = $this->service->validateUnitData($data);
        $this->assertTrue($result);
    }

    public function testValidateUnitDataWithMissingSourceLanguage()
    {
        $data = [
            // Missing sourceLanguage
            'targetLanguage' => 'fr',
            'sourceText' => 'Hello',
            'targetText' => 'Bonjour'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->service->validateUnitData($data);
    }
} 