<?php

namespace UrlShortener\Controllers;

use UrlShortener\Services\UrlService;

class UrlController {
    private $urlService;

    public function __construct(UrlService $urlService) {
        $this->urlService = $urlService;
    }

    public function create(int $userId, string $originalUrl): array {
        return $this->urlService->createShortUrl(
            originalUrl: $originalUrl, 
            userId: $userId
        );
    }

    public function getByShortCode(string $shortCode): array {
        return $this->urlService->getOriginalUrl(shortCode: $shortCode);
    }

    public function getUserUrls(int $userId): array {
        return $this->urlService->getUserUrls(userId: $userId);
    }

    public function update(int $urlId, int $userId, array $data): array {
        return $this->urlService->updateUrl(
            urlId: $urlId,
            userId: $userId, 
            data: $data
        );
    }

    public function delete(int $urlId, int $userId): array {
        return $this->urlService->deleteUrl(
            urlId: $urlId, 
            userId: $userId
        );
    }
} 