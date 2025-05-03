<?php

declare(strict_types=1);

namespace Application\Ports\Output;

/**
 * Interface for a consumer that consumes a log line.
 */
interface TextLineConsumerInterface
{
    public function consume(string $line): void;
}
