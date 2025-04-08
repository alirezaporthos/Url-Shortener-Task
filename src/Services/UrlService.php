<?php

namespace UrlShortener\Services;

use UrlShortener\Models\Url;
use UrlShortener\Repositories\UrlRepositoryInterface;

class UrlService {
    private $urlRepository;

    public function __construct(UrlRepositoryInterface $urlRepository) {
        $this->urlRepository = $urlRepository;
    }

    public function createShortUrl(string $originalUrl, int $userId): array {
        $url = new Url();
        $url->setOriginalUrl($originalUrl);
        $url->setUserId($userId);
        $url->setShortCode($url->generateShortCode());
        $url->setIsActive(true);

        if (!$url->validate()) {
            return [
                'success' => false,
                'errors' => $url->getErrors()
            ];
        }

        try {
            $urlId = $this->urlRepository->create(url: $url);
            $url->setId($urlId);

            return [
                'success' => true,
                'data' => $url->toArray()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Failed to create short URL']
            ];
        }
    }

    public function getOriginalUrl(string $shortCode): array {
        $url = $this->urlRepository->findByShortCode($shortCode);

        if (!$url || !$url->getIsActive()) {
            return [
                'success' => false,
                'errors' => ['URL not found or inactive']
            ];
        }

        $url->incrementClicks();
        $this->urlRepository->incrementClicks(url: $url);

        return [
            'success' => true,
            'data' => [
                'original_url' => $url->getOriginalUrl()
            ]
        ];
    }

    public function getUserUrls(int $userId): array {
        $urls = $this->urlRepository->getUserUrls($userId);

        return [
            'success' => true,
            'data' => array_map(function(Url $url) {
                return $url->toArray();
            }, $urls)
        ];
    }

    public function updateUrl(int $urlId, int $userId, array $data): array {
        $url = $this->urlRepository->findById(id: $urlId);

        if (!$url) {
            return [
                'success' => false,
                'errors' => ['URL not found']
            ];
        }

        if ($url->getUserId() !== $userId) {
            return [
                'success' => false,
                'errors' => ['Unauthorized to update this URL']
            ];
        }

        if (isset($data['original_url'])) {
            $url->setOriginalUrl(
                originalUrl: $data['original_url']
            );
        }
        if (isset($data['is_active'])) {
            $url->setIsActive(
                isActive: $data['is_active']
            );
        }

        if (!$url->validate()) {
            return [
                'success' => false,
                'errors' => $url->getErrors()
            ];
        }

        try {
            $this->urlRepository->update(url: $url);
            return [
                'success' => true,
                'data' => $url->toArray()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Failed to update URL']
            ];
        }
    }

    public function deleteUrl(int $urlId, int $userId): array {
        $url = $this->urlRepository->findById(id: $urlId);

        if (!$url) {
            return [
                'success' => false,
                'errors' => ['URL not found']
            ];
        }

        if ($url->getUserId() !== $userId) {
            return [
                'success' => false,
                'errors' => ['Unauthorized to delete this URL']
            ];
        }

        try {
            $this->urlRepository->delete(url: $url);
            return [
                'success' => true,
                'message' => 'URL deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['Failed to delete URL']
            ];
        }
    }
}