<?php

declare(strict_types=1);

namespace App\Adapter\Secondary;

use DateTimeInterface;
use App\Domain\LogParser\LogEntry;

class ElasticSearchJsonLogFormatter
{
    public function present(LogEntry $logEntry): string
    {
        return json_encode([
            'service' => $logEntry->service,
            'datetime' => $logEntry->datetime->format(DateTimeInterface::ATOM),
            'method' => $logEntry->method,
            'url' => $logEntry->url,
            'protocol' => $logEntry->protocol,
            'statusCode' => $logEntry->statusCode,
        ], JSON_THROW_ON_ERROR);
    }
}
