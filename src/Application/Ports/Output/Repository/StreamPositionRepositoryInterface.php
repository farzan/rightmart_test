<?php

declare(strict_types=1);

namespace Application\Ports\Output\Repository;

/**
 * Interface for a key-value storage to store stream position
 */
interface StreamPositionRepositoryInterface
{
    public function has(string $key): bool;
    
    public function get(string $key): string;
    
    public function set(string $key, string $value): void;
}
