<?php

namespace models;

use PDO;

// Base class for all models in app
class Model {

    public function __construct()
    {
        $this->db = self::getConnection();
    }

    // Generate connection using config file
    public static function getConnection()
    {
        $params = include('config.php');

        $connectionString = "mysql:host={$params['host']};dbname={$params['dbname']}";
        $db = new PDO($connectionString, $params['username'], $params['password']);
        
        return $db;
    }
}