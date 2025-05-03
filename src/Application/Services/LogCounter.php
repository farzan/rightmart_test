<?php

declare(strict_types=1);

namespace Application\Services;

use Exception;

class LogCounter
{
    public function __construct(
        private readonly LogCountQueryRunnerInterface $logCountQuery,
    ) {
    }
    
    public function query(LogCountQuery $query): LogCount
    {
        try {
            return $this->logCountQuery->query($query);
        } catch (Exception $e) {
            throw new OperationFailureException('Could not fulfill the operation.', previous: $e);
        }
    }
}
