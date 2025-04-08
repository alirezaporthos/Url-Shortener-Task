<?php

require_once __DIR__ . '/../vendor/autoload.php';

use UrlShortener\Controllers\AuthController;
use UrlShortener\Controllers\UrlController;
use UrlShortener\Middleware\AuthMiddleware;
use UrlShortener\Services\JwtService;
use UrlShortener\Services\UserService;
use UrlShortener\Services\UrlService;
use UrlShortener\Repositories\UserRepository;
use UrlShortener\Repositories\UrlRepository;
use UrlShortener\Core\Database;
use UrlShortener\Core\Cache;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();


// Initialize database connection
Database::getInstance();

// Initialize cache
Cache::getInstance();

// Initialize repositories
$userRepository = new UserRepository();
$urlRepository = new UrlRepository();

// Initialize services
$jwtService = new JwtService();
$userService = new UserService($userRepository, $jwtService);
$urlService = new UrlService($urlRepository);

// Initialize controllers
$authController = new AuthController($userService, $jwtService);
$urlController = new UrlController($urlService);

// Initialize middleware
$authMiddleware = new AuthMiddleware($jwtService);


return [
    'controllers' => [
        'auth' => $authController,
        'url' => $urlController
    ],
    'middleware' => [
        'auth' => $authMiddleware
    ]
]; 