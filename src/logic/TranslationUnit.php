<?php

namespace Core\Logic\Translation_unit;

use \Core\Models\TranslationUnitModel;

use PDO;

class TranslationUnit
{
    private $model;

    public function __construct(){
        $this->model = new TranslationUnitModel();
    }

    public function AddTranslationUnit(string $text , int $langId) : bool{
        return $this->model->AddTranslationUnit($text , $langId);
    }

    public function GetTranslationUnit($id) : ?array{
        return $this->model->GetTranslationUnit($id);
    }

    public function UpdateTranslationUnit($id , $text , $trans_text) : bool{
        return $this->model->UpdateTranslationUnit($id , $text , $trans_text);
    }

    public function DeleteTranslationUnit($id) : bool{
        return $this->model->DeleteTranslationUnit($id);
    }

    public function GetAllTranslationUnits() : ?array{
        return $this->model->GetAllTranslationUnits();
    }
}
