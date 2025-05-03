<?php

declare(strict_types=1);

namespace App\Domain\LogCounter;

use DateTimeInterface;

readonly class LogCountQuery
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
