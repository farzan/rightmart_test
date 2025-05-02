<?php

declare(strict_types=1);

namespace App\Domain\Port\DataService;

readonly class CountQueryResult
{
    public function __construct(
        public int $counter,
    ) {
    }
}
