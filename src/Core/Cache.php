<?php

namespace UrlShortener\Core;

use Predis\Client;

class Cache {
    private static $instance = null;
    private $client;

    private function __construct() {
        $config = require __DIR__ . '/../../config/redis.php';
        $this->client = new Client([
            'scheme' => $config['scheme'],
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => $config['database']
        ]);
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key): ?string {
        return $this->client->get($key);
    }

    public function set(string $key, string $value, int $ttl = 3600): void {
        $this->client->setex($key, $ttl, $value);
    }

    public function delete(string $key): void {
        $this->client->del($key);
    }

    public function exists(string $key): bool {
        return $this->client->exists($key) === 1;
    }
} 