<?php

declare(strict_types=1);

namespace Core\Interfaces;

interface DbInterface
{
    public function beginTransaction(): bool;
    public function commit(): bool;
    public function rollback(): bool;
    public function insert(string $query = "", array $params = []): int;
    public function select(string $query = "", array $params = []): array;
    public function selectOne(string $query = "", array $params = []): ?array;
    public function update(string $query = "", array $params = []): int;
    public function delete(string $query = "", array $params = []): int;
}
