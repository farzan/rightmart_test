<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\OperationFailureException;
use App\Domain\Port\Database\LogCountQueryRunnerInterface;
use App\Domain\Port\DataService\CountQueryResult;
use App\Domain\Port\DataService\LogCounterInterface;
use App\Domain\Port\DataService\CountQuery;
use Exception;

class LogCounter implements LogCounterInterface
{
    public function __construct(
        private readonly LogCountQueryRunnerInterface $logCountQuery,
    ) {
    }
    
    public function query(CountQuery $query): CountQueryResult
    {
        try {
            return $this->logCountQuery->query($query);
        } catch (Exception $e) {
            throw new OperationFailureException('Could not fulfill the operation.', previous: $e);
        }
    }
}
