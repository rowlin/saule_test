<?php

declare(strict_types=1);

namespace Core\Interfaces;

interface SessionInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): self;
    public function remove(string $key): void;
    public function clear(): void;
    public function has(string $key): bool;
}
