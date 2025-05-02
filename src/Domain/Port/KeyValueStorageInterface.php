<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface KeyValueStorageInterface
{
    public function has(string $key): bool;
    
    public function get(string $key): string;
    
    public function set(string $key, string $value): void;
}
