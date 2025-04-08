<?php

namespace UrlShortener\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService {
    private $secretKey;
    private $algorithm = 'HS256';
    private $expirationTime = 3600; // 1 hour

    public function __construct() {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
    }

    public function generateToken(array $userData): string {
        $issuedAt = time();
        $expiration = $issuedAt + $this->expirationTime;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiration,
            'data' => [
                'user_id' => $userData['id'],
                'email' => $userData['email']
            ]
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function validateToken(string $token): ?array {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getTokenFromHeader(): ?string {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authHeader = $headers['Authorization'];
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
} 