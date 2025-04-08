<?php

namespace UrlShortener\Repositories;

interface UserRepositoryInterface {
    public function create(array $data): int;
    public function findByEmail(string $email): ?array;
    public function findById(int $id): ?array;
} 