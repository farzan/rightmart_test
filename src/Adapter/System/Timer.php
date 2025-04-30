<?php

declare(strict_types=1);

namespace App\Adapter\System;

use App\Domain\Port\TimerInterface;

/**
 * @codeCoverageIgnore
 */
class Timer implements TimerInterface
{
    public function sleepMilliseconds(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }
}
