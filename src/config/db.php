<?php

return [
    'dsn' => "mysql:host=localhost;dbname=csv_db",
    'username' => 'root',
    'password' => 'root',
    'pdo_options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
];