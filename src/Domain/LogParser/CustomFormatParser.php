<?php

declare(strict_types=1);

namespace App\Domain\LogParser;

use DateTime;
use DateTimeInterface;

class CustomFormatParser
{
    private const string ENTRY_PATTERN = '/^([^\s]+) - - \[([^\]]+)\] "([A-Z]+) ([^\s]+) ([^"]+)" (\d+)/';
    
    private const string DATETIME_PATTERN = 'd/M/Y:H:i:s O';
    
    /**
     * @throws LogEntryException
     */
    public function parse(string $logEntry): CustomFormatEntry|null
    {
        if (preg_match(self::ENTRY_PATTERN, $logEntry, $matches)) {
            return new CustomFormatEntry(
                $matches[1],
                $this->createDateTime($matches[2], $logEntry),
                $matches[3],
                $matches[4],
                $matches[5],
                (int) $matches[6],
            );
        }
        
        throw LogEntryException::create('Invalid log format', $logEntry);
    }
    
    /**
     * @throws LogEntryException
     */
    private function createDateTime(string $datetimeString, string $logEntry): DateTimeInterface
    {
        $dateTime = DateTime::createFromFormat(self::DATETIME_PATTERN, $datetimeString);
        
        if ($dateTime === false) {
            throw LogEntryException::create('Invalid log format', $logEntry);
        }
        
        return $dateTime;
    }
}