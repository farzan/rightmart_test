<?php

declare(strict_types=1);

namespace App\Domain\Port\DataService;

interface LogCounterInterface
{
    public function query(CountQuery $query): CountQueryResult;
}
