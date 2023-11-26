<?php
 
// namespace Core\Tests\Logic\Translation_unit;

// require_once __DIR__ . '/../src/logic/TranslationUnit.php';

// include 'src';    
    
include 'src/models/Database.php';
use function Core\Models\InitializeDB;

InitializeDB();

include 'src/models/m_TranslationUnit.php';
use Core\Models\TranslationUnitModel;

class TranslationUnit
{
    private $model;

    public function __construct(){
        $this->model = new TranslationUnitModel();
    }

    public function AddTranslationUnit(string $text , int $langId, string $trans_text) : int {
        return $this->model->AddTranslationUnit($text , $langId, $trans_text);
    }

    public function GetTranslationUnit($id) : ?array{
        return $this->model->GetTranslationUnit($id);
    }

    public function UpdateTranslationUnit($id , $trans_text) : bool{
        return $this->model->UpdateTranslationUnit($id , $trans_text);
    }

    public function DeleteTranslationUnit($id) : bool{
        return $this->model->DeleteTranslationUnit($id);
    }

    public function GetAllTranslationUnits() : ?array{
        return $this->model->GetAllTranslationUnits();
    }
}

 
use PHPUnit\Framework\TestCase;

class TranslationUnitTest extends TestCase
{
    private $translationUnit;

    public function setUp(): void
    {
        $this->translationUnit = new TranslationUnit();
    }

    public function testAddTranslationUnit()
    {
        $this->assertEquals(1, $this->translationUnit->AddTranslationUnit('Hello, world!', 1, 'Hola, test!'));
    }

    public function testGetTranslationUnit()
    {
        $this->assertEquals([
            'id' => 1,
            'text' => 'Hello, world!',
            'langId' => 1,
            'trans_text' => 'Hola, test!',
        ], $this->translationUnit->GetTranslationUnit(1));
    }

    public function testUpdateTranslationUnit()
    {
        $this->assertTrue($this->translationUnit->UpdateTranslationUnit(1, 'Buenos, test!'));
    }

    public function testDeleteTranslationUnit()
    {
        $this->assertTrue($this->translationUnit->DeleteTranslationUnit(1));
    }

    public function testGetAllTranslationUnits()
    {
        $this->assertEquals([
            [
                'id' => 1,
                'text' => 'Hello, world!',
                'langId' => 1,
                'trans_text' => 'Hola, test!',
            ],
        ], $this->translationUnit->GetAllTranslationUnits());
    }
}