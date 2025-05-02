<?php

declare(strict_types=1);

namespace App\Domain\Port\Database;

use App\Domain\Port\DataService\CountQuery;
use App\Domain\Port\DataService\CountQueryResult;

interface LogCountQueryRunnerInterface
{
    public function query(CountQuery $query): CountQueryResult;
}
