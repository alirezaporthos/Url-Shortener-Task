<?php

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');



// Route handling
try {
    switch ($path) {
        case 'api/auth/register':
            echo 'register route';
            break;
        case 'api/auth/login':
            echo 'login route';
            break;
        case 'api/urls':
            echo 'urls route';
            break;
        default:
            echo "default route";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
} 