<?php

declare(strict_types=1);

namespace App\Adapter\LineConsumer;

use App\Domain\Port\TextLineConsumerInterface;

/**
 * This class is for debug purposes.
 *
 * @codeCoverageIgnore
 */
class LinePrinter implements TextLineConsumerInterface
{
    public function consume(string $line): void
    {
        print $line . PHP_EOL;
    }
}
