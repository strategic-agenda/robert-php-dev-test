<?php

class Translation {
    private $db;
    public function __construct()
    {
        $this->db = new Database;
    }

    public function getTranslations(){
        $this->db->query('SELECT *,
                            translations.id as translationId,
                            user.id as userId,
                            translations.created_at as translationCreated,
                            user.created_at as userCreated
                            FROM translations
                            INNER JOIN user
                            ON translations.user_id = user.id
                            ORDER BY translations.created_at DESC');
        $result = $this->db->resultSet();

        return $result;
    }

    public function addTranslation($data){
        $this->db->query('INSERT INTO translations(user_id, source, translation) VALUES (:user_id, :source, :translation)');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':source', $data['source']);
        $this->db->bind(':translation', $data['translation']);
        
        //execute 
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    public function getTranslationById($id){
        $this->db->query('SELECT * FROM translations WHERE id = :id');
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        return $row;
    }

    public function updateTranslation($data){
        $this->db->query('UPDATE translations SET source = :source, translation = :translation WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':source', $data['source']);
        $this->db->bind(':translation', $data['translation']);
        
        //execute 
        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }

    //delete a post
    public function deleteTranslation($id){
        $this->db->query('DELETE FROM translations WHERE id = :id');
        $this->db->bind(':id', $id);

        if($this->db->execute()){
            return true;
        }else{
            return false;
        }
    }
}