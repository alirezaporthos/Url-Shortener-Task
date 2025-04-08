<?php

namespace UrlShortener\Models;

use UrlShortener\Core\Database;

class User extends Model {
    protected $id;
    protected $username;
    protected $email;
    protected $password;
    protected $createdAt;
    protected $updatedAt;

    public function validate(): bool {
        $this->errors = [];

        if (empty($this->username)) {
            $this->addError('username', 'Username is required');
        } elseif (strlen($this->username) < 3) {
            $this->addError('username', 'Username must be at least 3 characters');
        }

        if (empty($this->email)) {
            $this->addError('email', 'Email is required');
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Invalid email format');
        }

        if (empty($this->password)) {
            $this->addError('password', 'Password is required');
        } elseif (strlen($this->password) < 6) {
            $this->addError('password', 'Password must be at least 6 characters');
        }

        return !$this->hasErrors();
    }

    public function hashPassword(): void {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password);
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }

    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setUsername(string $username): void {
        $this->username = $username;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
} 