<?php

declare(strict_types=1);

namespace Adapter\Secondary;

use Application\Ports\Output\TimeProviderInterface;

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
