<?php

return [
    'host' => $_ENV['DB_HOST'] ?? 'db',
    'dbname' => $_ENV['DB_DATABASE'] ?? 'url_shortener',
    'username' => $_ENV['DB_USERNAME'] ?? 'url_shortener',
    'password' => $_ENV['DB_PASSWORD'] ?? 'root',
    'charset' => 'utf8mb4'
];