<?php

declare(strict_types=1);

namespace AppTests\Integration\Adapter\Secondary;

use App\Adapter\Secondary\TextFileStream;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class TextFileStreamTest extends TestCase
{
    #[TestDox('Testing the stream with invalid filename')]
    public function testInvalidFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File does not exist: this_does_not_exist.log');
        
        new TextFileStream('this_does_not_exist.log');
    }
    
    #[TestDox('Read lines even if there isn\'t any')]
    public function testReadLines(): void
    {
        $stream = new TextFileStream( __DIR__ . '/test_file.txt');
        
        $this->assertSame('Line 1', $stream->read());
        $this->assertSame('Line 2', $stream->read());
        $this->assertNull($stream->read());
    }
    
    #[TestDox('Test getting the position and seek back to there')]
    public function testGetPositionAndSeek(): void
    {
        $stream1 = new TextFileStream( __DIR__ . '/test_file.txt');
        $stream1->read();
        $position = $stream1->getPosition();
        
        $stream2 = new TextFileStream( __DIR__ . '/test_file.txt');
        $stream2->seek($position);
        
        $this->assertSame('Line 2', $stream2->read());
    }
}
