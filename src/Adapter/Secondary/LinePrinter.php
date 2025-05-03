<?php

declare(strict_types=1);

namespace App\Adapter\Secondary;

use App\Application\Ports\Output\TextLineConsumerInterface;

/**
 * This class is for debug purposes.
 *
 * @todo remove
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
