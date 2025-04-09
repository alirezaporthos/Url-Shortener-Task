<?php

namespace UrlShortener\Models;

use UrlShortener\Models\Model;

class Url extends Model {
    protected $id;
    protected $userId;
    protected $originalUrl;
    protected $shortCode;
    protected $clicks = 0;
    protected $isActive = true;
    protected $createdAt;
    protected $updatedAt;

    private const BASE62_CHARS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const SHORT_CODE_LENGTH = 6;

    private const DEFAULT_CODE_LENGTH = 6;

    private function getShortCodeLength(): int {
        return (int)($_ENV['URL_LENGTH'] ?? self::DEFAULT_CODE_LENGTH);
    }

    public function validate(): bool {
        $this->errors = [];

        if (empty($this->originalUrl)) {
            $this->addError('original_url', 'Original URL is required');
        } elseif (!filter_var($this->originalUrl, FILTER_VALIDATE_URL)) {
            $this->addError('original_url', 'Invalid URL format');
        }

        if (empty($this->userId)) {
            $this->addError('user_id', 'User ID is required');
        }

        if (!empty($this->shortCode) && strlen($this->shortCode) > $this->getShortCodeLength()) {
            $this->addError('short_code', 'Short code is too long');
        }

        return empty($this->errors);
    }

    public function generateShortCode(): string {

        $uniqueId = time() . mt_rand(1000, 9999);
        
        // Convert to Base62
        $shortCode = '';
        $base = strlen(self::BASE62_CHARS);
        
        while ($uniqueId > 0) {
            $shortCode = self::BASE62_CHARS[$uniqueId % $base] . $shortCode;
            $uniqueId = (int)($uniqueId / $base);
        }
        
        // Pad with leading zeros if needed
        $shortCode = str_pad($shortCode, $this->getShortCodeLength(), '0', STR_PAD_LEFT);
        
        // Ensure the code is exactly SHORT_CODE_LENGTH characters
        return substr($shortCode, 0, $this->getShortCodeLength());
    }

    public function incrementClicks(): void {
        $this->clicks++;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'original_url' => $this->originalUrl,
            'short_code' => $this->shortCode,
            'clicks' => $this->clicks,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function getOriginalUrl(): ?string {
        return $this->originalUrl;
    }

    public function getShortCode(): ?string {
        return $this->shortCode;
    }

    public function getClicks(): int {
        return $this->clicks;
    }

    public function getIsActive(): bool {
        return $this->isActive;
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

    public function setUserId(int $userId): void {
        $this->userId = $userId;
    }

    public function setOriginalUrl(string $originalUrl): void {
        $this->originalUrl = $originalUrl;
    }

    public function setShortCode(string $shortCode): void {
        $this->shortCode = $shortCode;
    }

    public function setClicks(int $clicks): void {
        $this->clicks = $clicks;
    }

    public function setIsActive(bool $isActive): void {
        $this->isActive = $isActive;
    }

    public function setCreatedAt(string $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(string $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
} 