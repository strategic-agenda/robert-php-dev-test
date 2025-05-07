<?php

require_once 'DatabaseTestCase.php';
require_once __DIR__ . '/../src/Language.php';

class LanguageTest extends DatabaseTestCase
{
    private Language $language;

    protected function setUp(): void
    {
        parent::setUp();
        $this->language = new Language($this->pdo);
    }

    public function testGetAllReturnsSeededLanguages(): void
    {
        $result = $this->language->getAll();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertEquals('English', $result[0]['name']);
        $this->assertEquals('French', $result[1]['name']);
    }

    public function testGetAllReturnsEmptyArrayWhenNoLanguagesExist(): void
    {
        $this->pdo->exec("DELETE FROM languages");

        $result = $this->language->getAll();

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
