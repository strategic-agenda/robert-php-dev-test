<?php

class TranslationHistory {
    private $db;
    public function __construct()
    {
        $this->db = new Database;
    }

    public function addTranslationHistory($data){
        $this->db->query('INSERT INTO translation_histories(translation_id, old_json, new_json) VALUES (:translation_id, :old_json, :new_json)');
        $this->db->bind(':translation_id', $data['translation_id']);
        $this->db->bind(':old_json', $data['old_json']);
        $this->db->bind(':new_json', $data['new_json']);
        
        //execute 
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }
}
