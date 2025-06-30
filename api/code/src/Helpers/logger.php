<?php

// error, debug, audit, search, add, edit, delete

function logger($type = null, $message = null, $data = []) {
    $debug = (object) debug_backtrace()[0];

    if(empty($type)) {
        pd('type cannot be empty');
    }

    if(empty($message)) {
        pd('message cannot be empty');
    }

    if(empty($data)) {
        pd('data cannot be empty');
    }

    $logFolder = directory([__DIR__, '..', '..', 'storage', 'logs']);
    $logFile = DIRECTORY_SEPARATOR . $type . '.log';

    // pd($logFolder . $logFile);

    if(!is_file($logFolder . $logFile)) {
        try {
            fopen($logFolder . $logFile, 'w');
        } catch (Exception $e) {
            pd('log file cannot be created');
        }
    }

    if(!is_array($data)) {


        logger('warning', 'warning message', ['Logger data has to be an array.', $debug->file, $debug->line]);
        exit;
    }

    $log =  [
        'time' => date('YmdHis') . microtimeToSystem(microtime()),
        'file' => $debug->file,
        'line' => $debug->line,
        'data' => $data
    ];

    try {
        file_put_contents($logFolder . $logFile, json_encode($log) . PHP_EOL, FILE_APPEND | LOCK_EX);

        return true;
    } catch (\Exception $e) {
        pd('log content cannot be written');
    }
}
