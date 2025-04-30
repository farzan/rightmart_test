<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface TimerInterface
{
    public function sleepMilliseconds(int $milliseconds): void;
}
