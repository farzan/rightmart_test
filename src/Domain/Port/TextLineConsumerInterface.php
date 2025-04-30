<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface TextLineConsumerInterface
{
    public function consume(string $line): void;
}
