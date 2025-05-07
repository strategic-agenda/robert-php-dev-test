<?php

namespace Tests\Unit\Services;

use App\Interfaces\LanguageRepositoryInterface;
use App\Services\LanguageService;
use InvalidArgumentException;
use Tests\TestCase;

class LanguageServiceTest extends TestCase
{
    private $repository;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->mock(LanguageRepositoryInterface::class);
        $this->service = new LanguageService($this->repository);
    }

    public function testGetAll()
    {
        $expectedLanguages = [
            ['id' => 1, 'code' => 'en', 'name' => 'English'],
            ['id' => 2, 'code' => 'fr', 'name' => 'French']
        ];

        $this->repository->expects($this->once())
            ->method('getAll')
            ->willReturn($expectedLanguages);

        $result = $this->service->getAll();
        $this->assertEquals($expectedLanguages, $result);
    }

    public function testGetReturnsLanguage()
    {
        $languageId = 1;
        $expectedLanguage = ['id' => 1, 'code' => 'en', 'name' => 'English'];

        $this->repository->expects($this->once())
            ->method('getById')
            ->with($languageId)
            ->willReturn($expectedLanguage);

        $result = $this->service->get($languageId);
        $this->assertEquals($expectedLanguage, $result);
    }

    public function testGetReturnsNullWhenLanguageNotFound()
    {
        $languageId = 999;

        $this->repository->expects($this->once())
            ->method('getById')
            ->with($languageId)
            ->willReturn(null);

        $result = $this->service->get($languageId);
        $this->assertNull($result);
    }

    public function testGetByCodeReturnsLanguage()
    {
        $languageCode = 'en';
        $expectedLanguage = ['id' => 1, 'code' => 'en', 'name' => 'English'];

        $this->repository->expects($this->once())
            ->method('getByCode')
            ->with($languageCode)
            ->willReturn($expectedLanguage);

        $result = $this->service->getByCode($languageCode);
        $this->assertEquals($expectedLanguage, $result);
    }

    public function testCreateLanguage()
    {
        $languageData = ['code' => 'de', 'name' => 'German'];
        $expectedLanguage = array_merge(['id' => 3], $languageData);

        $this->repository->expects($this->once())
            ->method('languageExists')
            ->with('de')
            ->willReturn(false);

        $this->repository->expects($this->once())
            ->method('create')
            ->with($languageData)
            ->willReturn($expectedLanguage);

        $result = $this->service->create($languageData);
        $this->assertEquals($expectedLanguage, $result);
    }

    public function testCreateLanguageFailsWithExistingCode()
    {
        $languageData = ['code' => 'en', 'name' => 'English'];

        $this->repository->expects($this->once())
            ->method('languageExists')
            ->with('en')
            ->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->service->create($languageData);
    }

    public function testUpdateLanguage()
    {
        $languageId = 1;
        $languageData = ['name' => 'Updated English'];
        $language = ['id' => 1, 'code' => 'en', 'name' => 'English'];

        $this->repository->expects($this->once())
            ->method('getById')
            ->with($languageId)
            ->willReturn($language);

        $this->repository->expects($this->once())
            ->method('update')
            ->with($languageId, $languageData)
            ->willReturn(true);

        $result = $this->service->update($languageId, $languageData);
        $this->assertTrue($result);
    }

    public function testDeleteLanguage()
    {
        $languageId = 1;

        $this->repository->expects($this->once())
            ->method('delete')
            ->with($languageId)
            ->willReturn(true);

        $result = $this->service->delete($languageId);
        $this->assertTrue($result);
    }

    public function testValidateLanguageWithValidData()
    {
        $result = $this->service->validateLanguage('en', 'English');
        $this->assertTrue($result);
    }

    public function testValidateLanguageWithEmptyCode()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->validateLanguage('', 'English');
    }

    public function testValidateLanguageWithLongCode()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->validateLanguage('thisistoolong', 'English');
    }

    public function testValidateLanguageWithEmptyName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->validateLanguage('en', '');
    }
} 