<?php

declare(strict_types=1);

namespace App\Domain\LogCounter;

readonly class LogCount
{
    public function __construct(
        public int $counter,
    ) {
    }
}
