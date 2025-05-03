<?php

declare(strict_types=1);

namespace App\Adapter\Secondary;

use App\Application\Ports\Output\TimeProviderInterface;

/**
 * @codeCoverageIgnore
 */
class TimeProvider implements TimeProviderInterface
{
    public function sleepMilliseconds(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }
}
