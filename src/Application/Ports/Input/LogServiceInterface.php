<?php

declare(strict_types=1);

namespace App\Application\Ports\Input;

use App\Domain\LogCounter\LogCount;
use App\Domain\LogCounter\LogCountQuery;

interface LogServiceInterface
{
    public function getCount(LogCountQuery $query): LogCount;
    
    public function prepareStorage(): void;
}
