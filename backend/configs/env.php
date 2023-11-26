<?php

$env = [
    'DEBUG' => true,
    'DB_DSN' => 'mysql:host=mysql;dbname=intobi',
    'DB_USERNAME' => 'root',
    'DB_PASSWORD' => 'password',
];

foreach ($env as $key => $value) {
    $_ENV[strtoupper($key)] = $value;
}