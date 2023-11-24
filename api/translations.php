<?php

use Core\Logic\Translation_unit\TranslationUnit;
use function Core\Models\InitializeDB;

InitializeDB();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Splitting the URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
// 

// Checking if the request is valid 
$valid = isset($uri[2]);

if (!$valid){
    header("HTTP/1.1 404 Not Found");
    exit();
}

if ($valid !== 'Add' && $valid !== 'Update' && $valid !== 'Delete' && $valid !== 'Get'){
    header("HTTP/1.1 404 Not Found");
    exit();
}

if (($valid == 'Add' || $valid == 'Update' || $valid == 'Delete') && $_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("HTTP/1.1 404 Not Found");
    exit();
}

if ($valid == 'Get' && $_SERVER['REQUEST_METHOD'] !== 'GET'){
    header("HTTP/1.1 404 Not Found");
    exit();
}
// 

$unit = new TranslationUnit();

if ($valid == 'Add'){
    if ( !isset($_POST["text"]) || !isset($_POST["langId"]) ){
        header("HTTP/1.1 400 Bad Request");
        exit();
    }

    $text   = htmlspecialchars($_POST["text"]);
    $langId = (int)htmlspecialchars($_POST["langId"]);

    $result = $unit->AddTranslationUnit($text , $langId);

    if (!$result){
        header("HTTP/1.1 500 Internal Server Error");
        exit();  
    }

    header("HTTP/1.1 201 Created");
    exit();  
}

if ($valid == 'Update'){
    if ( !isset($_POST["text"]) || !isset($_POST["id"]) || !isset($_POST["trans_text"]) ){
        header("HTTP/1.1 400 Bad Request");
        exit();
    }

    $id         = (int)htmlspecialchars($_POST["id"]);
    $text       = htmlspecialchars($_POST["text"]);
    $trans_text = htmlspecialchars($_POST["trans_text"]);

    $result = $unit->UpdateTranslationUnit($id , $text , $trans_text);

    if (!$result){
        header("HTTP/1.1 500 Internal Server Error");
        exit();  
    }

    header("HTTP/1.1 201 Created");
    exit();  
}

if ($valid == 'Delete'){
    if (!isset($_POST["id"])){
        header("HTTP/1.1 400 Bad Request");
        exit();
    }

    $id         = (int)htmlspecialchars($_POST["id"]);

    $result     = $unit->DeleteTranslationUnit($id);

    if (!$result){
        header("HTTP/1.1 500 Internal Server Error");
        exit();  
    }

    header("HTTP/1.1 201 Created");
    exit();  
}

if ($valid == 'Get'){
    if (!isset($_POST["id"])){
        header("HTTP/1.1 400 Bad Request");
        exit();
    }

    $id         = (int)htmlspecialchars($_POST["id"]);
    
    if ($id == -1){
        $result = $unit->GetAllTranslationUnits();
    }else{
        $result = $unit->GetTranslationUnit($id);
    }

    if (!$result){
        header("HTTP/1.1 500 Internal Server Error");
        exit();  
    }

    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}