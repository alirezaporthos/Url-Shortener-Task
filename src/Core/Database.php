<?php

namespace UrlShortener\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $connection = null;
    private $transactionLevel = 0;
    private $config;

    private function __construct() {
        $this->loadConfig();
    }

    private function loadConfig() {
        $this->config = require __DIR__ . '/../../config/database.php';
        
        // Validate configuration
        $requiredKeys = ['host', 'dbname', 'username', 'password', 'charset'];
        foreach ($requiredKeys as $key) {
            if (!isset($this->config[$key])) {
                throw new \RuntimeException("Missing required database configuration key: {$key}");
            }
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset={$this->config['charset']}";
                $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    // Enable persistent connections for connection pooling
                    PDO::ATTR_PERSISTENT => true,
                    // Set connection timeout
                    PDO::ATTR_TIMEOUT => 5
                ]);
            } catch (PDOException $e) {
                throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
            }
        }
        return $this->connection;
    }

    public function getConnection(): PDO {
        return $this->connect();
    }

    public function beginTransaction(): bool {
        if ($this->transactionLevel === 0) {
            $this->connect()->beginTransaction();
        }
        $this->transactionLevel++;
        return true;
    }

    public function commit(): bool {
        if ($this->transactionLevel === 0) {
            throw new \RuntimeException('No active transaction to commit');
        }

        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            return $this->connect()->commit();
        }
        return true;
    }

    public function rollBack(): bool {
        if ($this->transactionLevel === 0) {
            throw new \RuntimeException('No active transaction to roll back');
        }

        $this->transactionLevel--;
        if ($this->transactionLevel === 0) {
            return $this->connect()->rollBack();
        }
        return true;
    }

    public function prepare(string $sql): \PDOStatement {
        return $this->connect()->prepare($sql);
    }

    public function lastInsertId(): string {
        return $this->connect()->lastInsertId();
    }

    public function inTransaction(): bool {
        return $this->transactionLevel > 0;
    }

    public function __destruct() {
        // Rollback any uncommitted transactions before closing
        if ($this->inTransaction()) {
            $this->connect()->rollBack();
        }
        $this->connection = null;
    }

    // Prevent cloning of the instance
    private function __clone() {}

}