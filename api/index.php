<?php




require_once 'translations.php';

//log errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// POST /api/translation-units
// Content-Type: application/json

// {
//   "document_id": 1,
//   "source_text": "Hello world",
//   "target_text": "Hola mundo",
//   "status": "new"
// }

