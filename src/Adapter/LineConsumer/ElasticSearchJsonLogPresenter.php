<?php

declare(strict_types=1);

namespace App\Adapter\LineConsumer;

use App\Domain\LogParser\CustomFormatEntry;
use DateTimeInterface;

class ElasticSearchJsonLogPresenter
{
    public function present(CustomFormatEntry $logEntry): string
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
