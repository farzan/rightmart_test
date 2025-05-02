<?php

declare(strict_types=1);

namespace App\Domain\Port\DataService;

use DateTimeInterface;

readonly class CountQuery
{
    /**
     * @param list<string>|null $serviceNames
     */
    public function __construct(
        public array|null $serviceNames,
        public int|null $statusCode,
        public DateTimeInterface|null $startDate,
        public DateTimeInterface|null $endDate,
    ) {
    }
}
