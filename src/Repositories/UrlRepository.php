<?php

namespace UrlShortener\Repositories;

use UrlShortener\Core\Database;
use UrlShortener\Core\Cache;
use UrlShortener\Models\Url;
use PDOException;

class UrlRepository implements UrlRepositoryInterface {
    private $db;
    private $cache;
    private const CACHE_TTL = 3600;
    private const MAX_RETRIES = 3;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->cache = Cache::getInstance();
    }

    public function create(Url $url): bool {
        $retries = 0;
        while ($retries < self::MAX_RETRIES) {
            try {
                $this->db->beginTransaction();

                // Check if short code already exists
                $checkStmt = $this->db->prepare(sql: "SELECT id FROM urls WHERE short_code = ? FOR UPDATE");
                $checkStmt->execute(params: [$url->getShortCode()]);
                if ($checkStmt->fetch()) {
                    $this->db->rollBack();
                    // Generate a new short code and retry
                    $url->generateShortCode();
                    $retries++;
                    continue;
                }

                // Insert new URL
                $sql = "INSERT INTO urls (user_id, original_url, short_code, is_active) VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare(sql: $sql);
                $result = $stmt->execute(params: [
                    $url->getUserId(),
                    $url->getOriginalUrl(),
                    $url->getShortCode(),
                    $url->getIsActive()
                ]);

                if ($result) {
                    $url->setId((int)$this->db->lastInsertId());
                    $this->db->commit();
                    return true;
                }

                $this->db->rollBack();
                return false;

            } catch (PDOException $e) {
                $this->db->rollBack();
                if ($e->getCode() == 23000) { // MySQL duplicate entry error
                    $retries++;
                    continue;
                }
                throw $e;
            }
        }
        
        throw new \RuntimeException('Failed to generate unique short code after ' . self::MAX_RETRIES . ' attempts');
    }

    public function findByShortCode(string $shortCode): ?Url {
        $cacheKey = "url:{$shortCode}";
        
        // Try to get from cache first
        $cachedUrl = $this->cache->get(key: $cacheKey);
        if ($cachedUrl !== null) {
            $data = json_decode($cachedUrl, true);
            $url = new Url();
            $url->setId($data['id']);
            $url->setUserId($data['user_id']);
            $url->setOriginalUrl($data['original_url']);
            $url->setShortCode($data['short_code']);
            $url->setClicks($data['clicks']);
            $url->setIsActive((bool)$data['is_active']);
            $url->setCreatedAt($data['created_at']);
            $url->setUpdatedAt($data['updated_at']);
            return $url;
        }

        try {
            $sql = "SELECT * FROM urls WHERE short_code = ? AND is_active = TRUE";
            $stmt = $this->db->prepare(sql: $sql);
            $stmt->execute(params: [$shortCode]);
            $data = $stmt->fetch();

            if (!$data) {
                return null;
            }

            $url = new Url();
            $url->setId($data['id']);
            $url->setUserId($data['user_id']);
            $url->setOriginalUrl($data['original_url']);
            $url->setShortCode($data['short_code']);
            $url->setClicks($data['clicks']);
            $url->setIsActive((bool)$data['is_active']);
            $url->setCreatedAt($data['created_at']);
            $url->setUpdatedAt($data['updated_at']);
            
            $this->cache->set(key: $cacheKey, value: json_encode($data), ttl: self::CACHE_TTL);
            return $url;

        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function findById(int $id): ?Url {
        $cacheKey = "url:id:{$id}";
        
        // Try to get from cache first
        $cachedUrl = $this->cache->get(key: $cacheKey);
        if ($cachedUrl !== null) {
            $data = json_decode($cachedUrl, true);
            $url = new Url();
            $url->setId($data['id']);
            $url->setUserId($data['user_id']);
            $url->setOriginalUrl($data['original_url']);
            $url->setShortCode($data['short_code']);
            $url->setClicks($data['clicks']);
            $url->setIsActive((bool)$data['is_active']);
            $url->setCreatedAt($data['created_at']);
            $url->setUpdatedAt($data['updated_at']);
            return $url;
        }

        try {
            $sql = "SELECT * FROM urls WHERE id = ?";
            $stmt = $this->db->prepare(sql: $sql);
            $stmt->execute(params: [$id]);
            $data = $stmt->fetch();

            if (!$data) {
                return null;
            }

            $url = new Url();
            $url->setId($data['id']);
            $url->setUserId($data['user_id']);
            $url->setOriginalUrl($data['original_url']);
            $url->setShortCode($data['short_code']);
            $url->setClicks($data['clicks']);
            $url->setIsActive((bool)$data['is_active']);
            $url->setCreatedAt($data['created_at']);
            $url->setUpdatedAt($data['updated_at']);
            
            $this->cache->set(key: $cacheKey, value: json_encode($data), ttl: self::CACHE_TTL);
            return $url;

        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function getUserUrls(int $userId): array {
        try {
            $sql = "SELECT * FROM urls WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $this->db->prepare(sql: $sql);
            $stmt->execute(params: [$userId]);
            $urls = [];
            
            while ($data = $stmt->fetch()) {
                $url = new Url();
                $url->setId($data['id']);
                $url->setUserId($data['user_id']);
                $url->setOriginalUrl($data['original_url']);
                $url->setShortCode($data['short_code']);
                $url->setClicks($data['clicks']);
                $url->setIsActive((bool)$data['is_active']);
                $url->setCreatedAt($data['created_at']);
                $url->setUpdatedAt($data['updated_at']);
                $urls[] = $url;
            }

            return $urls;
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function update(Url $url): bool {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE urls SET original_url = ?, is_active = ? WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare(sql: $sql);
            $result = $stmt->execute(params: [
                $url->getOriginalUrl(),
                $url->getIsActive(),
                $url->getId(),
                $url->getUserId()
            ]);

            if ($result) {
                // Invalidate cache
                $this->cache->delete(key: "url:{$url->getShortCode()}");
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(Url $url): bool {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM urls WHERE id = ? AND user_id = ?";
            $stmt = $this->db->prepare(sql: $sql);
            $result = $stmt->execute(params: [$url->getId(), $url->getUserId()]);

            if ($result) {
                // Invalidate cache
                $this->cache->delete(key: "url:{$url->getShortCode()}");
                $this->db->commit();
                return true;
            }

            $this->db->rollBack();
            return false;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function incrementClicks(Url $url): bool {
        try {
            $sql = "UPDATE urls SET clicks = clicks + 1 WHERE id = ?";
            $stmt = $this->db->prepare(sql: $sql);
            $result = $stmt->execute(params: [$url->getId()]);

            if ($result) {
                $url->incrementClicks();
                // Update cache
                $this->cache->delete(key: "url:{$url->getShortCode()}");
                return true;
            }

            return false;
        } catch (PDOException $e) {
            // Log error but don't throw - this is not critical
            return false;
        }
    }
} 