<?php
  
include '../src/logic/TranslationUnit.php';
use Core\Logic\Translation_unit\TranslationUnit; 

include '../src/models/Database.php';
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
$is_valid = isset($uri[3]);
$valid = $uri[3];


if (!$is_valid){ 
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

    $jsonInput = file_get_contents("php://input"); 
    $data = json_decode($jsonInput, true); 
  
    if ( !isset($data["text"]) || !isset($data["langId"]) ){
        header("HTTP/1.1 400 Bad Request");
        exit();
    }


    $text   = htmlspecialchars($data["text"]);
    $langId = (int)htmlspecialchars($data["langId"]);

    $result = $unit->AddTranslationUnit($text , $langId);

    if (!$result){
        header("HTTP/1.1 500 Internal Server Error");
        exit();  
    }

    header("HTTP/1.1 201 Created");  
    echo json_encode( ["message" => "Translation Unit Added Successfully"] );
    exit();  
}

if ($valid == 'Update'){ 
    $jsonInput = file_get_contents("php://input");
    $data = json_decode($jsonInput, true); 

    if (!isset($data["id"]) || !isset($data["trans_text"]) ){
        header("HTTP/1.1 400 Bad Request");
        exit();
    }
 
    $id         = (int)htmlspecialchars($data["id"]); 
    $trans_text = htmlspecialchars($data["trans_text"]);

    $result = $unit->UpdateTranslationUnit($id , $trans_text);

    if (!$result){
        header("HTTP/1.1 500 Internal Server Error");
        exit();  
    }

    header('Content-Type: application/json');
    header("HTTP/1.1 201 Created");
    echo json_encode( ["message" => "Translation Unit Updated Successfully"] );
    exit();  
}

if ($valid == 'Delete'){
    $jsonInput = file_get_contents("php://input");
    $data = json_decode($jsonInput, true); 

    if (!isset($data["id"])){
        header("HTTP/1.1 400 Bad Request");
        exit();
    }
  
    $id         = (int)htmlspecialchars($data["id"]);

    $result     = $unit->DeleteTranslationUnit($id);

    if (!$result){
        header("HTTP/1.1 500 Internal Server Error");
        exit();  
    }

    header("HTTP/1.1 201 Created");
    exit();  
}

if ($valid == 'Get'){

    if (!isset($_GET["id"])){
        header("HTTP/1.1 400 Bad Request");
        exit();
    }

    $id         = (int)htmlspecialchars($_GET["id"]);
    
    if ($id == -1){
        $result = $unit->GetAllTranslationUnits();
    }else{
        $result = $unit->GetTranslationUnit($id);
    }
 
    // if (!$result){
    //     header("HTTP/1.1 500 Internal Server Error");
    //     exit();  
    // }
 
    header('Content-Type: application/json');
    echo json_encode($result);
    exit();
}