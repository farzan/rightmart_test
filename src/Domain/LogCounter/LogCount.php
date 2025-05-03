<?php

declare(strict_types=1);

namespace Domain\LogCounter;

readonly class LogCount
{
    public function __construct(
        public int $counter,
    ) {
    }
}
