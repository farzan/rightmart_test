<?php

declare(strict_types=1);

namespace AppTests\Unit\Domain\LogParser;

use App\Application\Services\LogEntryException;
use App\Application\Services\LogEntryParser;
use AppTests\Support\UnitTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\TestDox;

class CustomCommonLogFormatParserTest extends UnitTestCase
{
    #[TestDox('Parse valid log entry')]
    public function testParseValidLogEntry(): void
    {
        $entry = 'USER-SERVICE - - [17/Aug/2018:09:21:53 +0200] "POST /users HTTP/1.1" 201';
        
        $parser = new LogEntryParser();
        $parsedEntry = $parser->parse($entry);
        
        $this->assertNotNull($parsedEntry);
        $this->assertSame('USER-SERVICE', $parsedEntry->service);
        $this->assertSame(
            (new DateTimeImmutable('2018-08-17 09:21:53 +0200'))->getTimestamp(),
            $parsedEntry->datetime->getTimestamp(),
        );
        $this->assertSame('POST', $parsedEntry->method);
        $this->assertSame('/users', $parsedEntry->url);
        $this->assertSame('HTTP/1.1', $parsedEntry->protocol);
        $this->assertSame(201, $parsedEntry->statusCode);
    }
    
    public function testInvalidFormat(): void
    {
        $this->expectException(LogEntryException::class);
        
        // No status code:
        $entry = 'USER-SERVICE - - [17/Aug/2018:09:21:53 +0200] "POST /users HTTP/1.1"';
        $parser = new LogEntryParser();
        $parser->parse($entry);
    }
    
    public function testInvalidDate(): void
    {
        $this->expectException(LogEntryException::class);
        
        // Invalid month:
        $entry = 'USER-SERVICE - - [17/Aux/2018:09:21:53 +0200] "POST /users HTTP/1.1" 201';
        $parser = new LogEntryParser();
        $parser->parse($entry);
    }
}
