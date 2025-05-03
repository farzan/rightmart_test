<?php

declare(strict_types=1);

namespace App\Application\Ports\Output\Repository;

use App\Domain\LogCounter\LogCount;
use App\Domain\LogCounter\LogCountQuery;

interface LogRepositoryInterface
{
    public function prepare(): void;
    
    public function query(LogCountQuery $query): LogCount;
}
