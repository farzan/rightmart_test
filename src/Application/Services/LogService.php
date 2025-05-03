<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Input\LogServiceInterface;
use App\Application\Ports\Output\Repository\LogRepositoryInterface;
use App\Domain\LogCounter\LogCount;
use App\Domain\LogCounter\LogCountQuery;

class LogService implements LogServiceInterface
{
    public function __construct(
        private readonly LogRepositoryInterface $logRepository,
    ) {
    }
    
    public function getCount(LogCountQuery $query): LogCount
    {
        return $this->logRepository->query($query);
    }
    
    public function prepareStorage(): void
    {
        $this->logRepository->prepare();
    }
}
