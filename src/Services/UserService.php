<?php

namespace UrlShortener\Services;

use UrlShortener\Repositories\UserRepositoryInterface;
use UrlShortener\Services\JwtService;

class UserService {
    private $userRepository;
    private $jwtService;

    public function __construct(UserRepositoryInterface $userRepository, JwtService $jwtService) {
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
    }
    public function register(array $data): array {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        if ($this->userRepository->findByEmail(email: $data['email'])) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        $userId = $this->userRepository->create(data: $data);
        $user = $this->userRepository->findById(id: $userId);
        unset($user['password']);

        $token = $this->jwtService->generateToken(userData: $user);

        return [
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ];
    }

    public function login(array $data): array {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        $user = $this->userRepository->findByEmail(email: $data['email']);
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        if (!password_verify($data['password'], $user['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        unset($user['password']);
        $token = $this->jwtService->generateToken(userData: $user);

        return [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ];
    }
} 