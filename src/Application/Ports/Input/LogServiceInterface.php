<?php

declare(strict_types=1);

namespace Application\Ports\Input;

use Domain\LogCounter\LogCount;
use Domain\LogCounter\LogCountQuery;

interface LogServiceInterface
{
    public function getCount(LogCountQuery $query): LogCount;
    
    public function prepareStorage(): void;
}
