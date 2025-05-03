<?php

declare(strict_types=1);

namespace App\Domain\LogParser;

use DateTimeInterface;

/**
 * @codeCoverageIgnore
 */
readonly class LogEntry
{
    public function __construct(
        public string $service,
        public DateTimeInterface $datetime,
        public string $method,
        public string $url,
        public string $protocol,
        public int $statusCode,
    ) {
    }
}
