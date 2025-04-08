<?php

namespace UrlShortener\Repositories;

use UrlShortener\Models\Url;

interface UrlRepositoryInterface {
    public function create(Url $url): bool;
    public function findByShortCode(string $shortCode): ?Url;
    public function findById(int $id): ?Url;
    public function getUserUrls(int $userId): array;
    public function update(Url $url): bool;
    public function delete(Url $url): bool;
    public function incrementClicks(Url $url): bool;
} 