<?php

namespace Core\Models;

include 'config.ini';
use PDO;
use PDOException;

$Database = null;

class DatabaseConnection
{
    private $dbHost; 
    private $dbName; 
    private $dbUsername; 
    private $dbPassword; 
    private $dsn;
    private $dbConn;

    public function __construct(){
        $config = parse_ini_file('config.ini');
        $this->dbHost     = $config['database']['host'];
        $this->dbName     = $config['database']['database'];
        $this->dbUsername = $config['database']['username'];
        $this->dbPassword = $config['database']['password'];
        $this->dsn        = sprintf("mysql:host=%s;dbname=%s;port=3306;charset=utf8" , $this->dbHost , $this->dbName);

        $this->dbConn     = $this->Connect();
    }   

    public function Connect() : PDO {
        try{
            $conn = new PDO(
                $this->dsn,
                $this->dbUsername,
                $this->dbPassword
            );

            return $conn;

        } catch( PDOException $e){
            error_log("PDO connection failed: " . $e->getMessage());
            return null;
        }   
    }

    public function GetConnection() : PDO {
        return $this->dbConn;
    }
}

function InitializeDB() : bool {
    global $Database;
    $Database = new DatabaseConnection();
    return true;
}

function GetConnection() : PDO {
    global $Database;
    if ($Database == null){
        return null;
    }

    return $Database->GetConnection();
}
