<?php

declare(strict_types=1);

namespace App\Application\Ports\Output;

/**
 * Interface for a consumer that consumes a log line.
 */
interface LogLineRepositoryInterface
{
    public function consume(string $line): void;
}
