<?php

declare(strict_types=1);

namespace Application\Services;

use Application\Ports\Input\LogServiceInterface;
use Application\Ports\Output\Repository\LogRepositoryInterface;
use Domain\LogCounter\LogCount;
use Domain\LogCounter\LogCountQuery;

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
