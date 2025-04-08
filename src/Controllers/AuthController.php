<?php

namespace UrlShortener\Controllers;

use UrlShortener\Services\UserService;

class AuthController {
    private $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function register(array $data): array {
        return $this->userService->register(
            data: $data
        );
    }

    public function login(array $data): array {
        return $this->userService->login(
            data: $data
        );
    }
} 