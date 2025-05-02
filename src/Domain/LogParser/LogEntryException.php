<?php

declare(strict_types=1);

namespace App\Domain\LogParser;

use Exception;

class LogEntryException extends Exception
{
    private string $logEntry;
    
    public static function create(string $message, string $logEntry): self
    {
        $e = new self($message);
        $e->logEntry = $logEntry;
        
        return $e;
    }
    
    public function getLogEntry(): string
    {
        return $this->logEntry;
    }
}
