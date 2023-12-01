<?php

use PHPUnit\Framework\TestCase;
use models\TranslationUnit;


// PHPUnit class for testing TranslationUnit class
class TranslationUnitTest extends TestCase
{
    public function testGet()
    {
        $translationUnit = new TranslationUnit();

        $result = $translationUnit->get(2);
        $result = json_decode($result, true);

        if ($result) {
            $this->assertIsArray($result);
        } else {
            $this->assertFalse($result, 'Expected result to be false when ID is not found');
        }
    }

    public function testDelete()
    {
        $translationUnit = new TranslationUnit();

        $result = $translationUnit->delete(1);

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('{"status": 200, "message": "Translation unit deleted successfully"}', $result);
    }

    public function testList()
    {
        $translationUnit = new TranslationUnit();
        $result = $translationUnit->list();

        $this->assertJson($result);
    }

    public function testSave()
    {
        $translationUnit = new TranslationUnit();
        
        $data = [
            'text' => 'Test',
            'type' => 'word',
            'languageCode' => 'en',
        ];

        $result = $translationUnit->save($data);

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('{"status": 200, "message": "Translation unit saved successfully"}', $result);
    }
}