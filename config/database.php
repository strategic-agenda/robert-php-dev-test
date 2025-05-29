<?php

function getDatabase() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }
    return $db;
} 