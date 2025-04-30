<?php

declare(strict_types=1);

namespace App\Tests\Unit\Adapter;

use App\Adapter\Io\TextFileStream;
use App\Tests\Support\UnitTestCase;
use InvalidArgumentException;

class TextFileStreamTest extends UnitTestCase
{
    public function testInvalidFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File does not exist: this_does_not_exist.log');
        
        new TextFileStream('this_does_not_exist.log');
    }
    
    public function testReadLines(): void
    {
        $stream = new TextFileStream( __DIR__ . '/test_file.txt');
        
        $this->assertSame('Line 1', $stream->read());
        $this->assertSame('Line 2', $stream->read());
        $this->assertNull($stream->read());
    }
    
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
