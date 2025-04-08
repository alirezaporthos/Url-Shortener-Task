<?php

return [
    'scheme' => $_ENV['REDIS_SCHEME'] ?? 'tcp',
    'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
    'port' => $_ENV['REDIS_PORT'] ?? 6379,
    'database' => $_ENV['REDIS_DB'] ?? 0
]; 