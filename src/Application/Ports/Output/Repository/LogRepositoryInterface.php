<?php

declare(strict_types=1);

namespace Application\Ports\Output\Repository;

use Domain\LogCounter\LogCount;
use Domain\LogCounter\LogCountQuery;

interface LogRepositoryInterface
{
    public function prepare(): void;
    
    public function query(LogCountQuery $query): LogCount;
}
