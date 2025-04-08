<?php

namespace UrlShortener\Middleware;

use UrlShortener\Services\JwtService;

class AuthMiddleware {
    private $jwtService;

    public function __construct(JwtService $jwtService) {
        $this->jwtService = $jwtService;
    }

    public function handle(): ?array {
        $token = $this->jwtService->getTokenFromHeader();
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }

        $userData = $this->jwtService->validateToken($token);
        
        if (!$userData) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }

        return $userData;
    }
} 