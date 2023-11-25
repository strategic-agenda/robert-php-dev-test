<?php

namespace Core\Models;

use Exception;
use PDO;

class TranslationUnitModel{
    private $dbConn;

    public function __construct(){

        $this->dbConn = GetConnection();

    }


    public function AddTranslationUnit(string $unit_text , int $language_id, string $trans_text) : int {
        $stmt = $this->dbConn->prepare(
            "INSERT INTO translation_units (unit_text, language_id, translated_text) VALUES (?, ?, ?)"
        );
        
        $stmt->execute([$unit_text , $language_id, $trans_text]);
         
        $LastIdInserted = $this->dbConn->lastInsertId();
  
        return $LastIdInserted;
    }

    public function AddTranslationUnitRecord(int $translation_unit_id , int $translation_version , int $translated_text) : bool {
        $stmt = $this->dbConn->prepare(
            "INSERT INTO translation_unit_records (translation_unit_id, translation_version , translated_text) VALUES (?, ?, ?)"
        );
        $stmt->execute([$translation_unit_id , $translation_version , $translated_text]);

        return true;
    }

    public function UpdateTranslationUnit(int $unit_id , string $translated_text ) : bool {
        
        $this->dbConn->beginTransaction();
        try{
            $unit = $this->GetTranslationUnit($unit_id);

            $current_version    = $unit['translation_version'];
            // $current_version    = 1; 
            // $translated_text    = $unit['translated_text'];
            $translated_text    = $translated_text;

            $stmt = $this->dbConn->prepare(
                "UPDATE `translation_units` SET `translated_text` = ?, `translation_version` = ? WHERE id = ?"
            );

            $stmt->execute([$translated_text , $current_version + 1, $unit_id]);

            $stmt = $this->dbConn->prepare(
                "INSERT INTO translation_unit_records (translation_unit_id, translation_version , translated_text) VALUES (?, ?, ?)"
            );
            $stmt->execute([$unit_id , $current_version , $translated_text]);

            $this->dbConn->commit();
            
            return true;
        }catch (Exception $e){
            $this->dbConn->rollBack();
            error_log("Update translation unit failed : " . $e->getMessage());
            return false;
        }
    }

    public function GetTranslationUnit(int $id) : ?array {
        $stmt = $this->dbConn->prepare(
            "SELECT * FROM translation_units WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function GetAllTranslationUnits() : ?array {
        $stmt = $this->dbConn->prepare(
            "SELECT * FROM translation_units"
        );
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    public function GetTranslationUnitRecord($id) : ?array {
        $stmt = $this->dbConn->prepare(
            "SELECT * FROM translation_unit_records WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function GetTranslationUnitRecordByUnIDandVersion($id , $version) : ?array {
        $stmt = $this->dbConn->prepare(
            "SELECT * FROM translation_unit_records WHERE id = ? and translation_version = ?"
        );
        $stmt->execute([$id , $version]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function GetAllTranslationUnitRecords($id) : ?array {
        $stmt = $this->dbConn->prepare(
            "SELECT * FROM translation_unit_records WHERE id = ?"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function DeleteTranslationUnit($id) : bool {
        try{
            $stmt = $this->dbConn->prepare(
                "DELETE FROM translation_units WHERE id=?"
            );

            $stmt->execute([$id]);

            return true;
        }catch (Exception $e){
            error_log("Delete translation unit failed : " . $e->getMessage());
            return false;
        }
    }

    public function DeleteTranslationUnitRecordByUnIDandVersion($unit_id , $version) : bool {
        try{
            $stmt = $this->dbConn->prepare(
                "DELETE FROM translation_unit_records WHERE translation_unit_id=? and translation_version=?"
            );

            $stmt->execute([$unit_id , $version]);

            return true;
        }catch (Exception $e){
            error_log("Delete translation unit record by og_unit_id and version failed : " . $e->getMessage());
            return false;
        }
    }

    public function DeleteTranslationUnitRecord($id) : bool {
        try{
            $stmt = $this->dbConn->prepare(
                "DELETE FROM translation_unit_records WHERE id=?"
            );

            $stmt->execute([$id]);

            return true;
        }catch (Exception $e){
            error_log("Delete translation unit record failed : " . $e->getMessage());
            return false;
        }
    }

    public function DeleteTranslationUnitRecords($unit_id) : bool {
        try{
            $stmt = $this->dbConn->prepare(
                "DELETE FROM translation_unit_records WHERE translation_unit_id=?"
            );

            $stmt->execute([$unit_id]);

            return true;
        }catch (Exception $e){
            error_log("Delete of all translation unit records failed : " . $e->getMessage());
            return false;
        }
    }
}