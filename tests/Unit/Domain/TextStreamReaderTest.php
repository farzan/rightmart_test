<?php

declare(strict_types=1);

namespace AppTests\Unit\Domain;

use Application\Ports\Input\TextStreamInterface;
use Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use Application\Ports\Output\TextLineConsumerInterface;
use Application\Ports\Output\TimeProviderInterface;
use Application\Services\TextStreamReader;
use AppTests\Support\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

class TextStreamReaderTest extends UnitTestCase
{
    #[TestDox('Start reading with 4 lines, and then stop after 5 reads')]
    public function testStartThenRead4LinesThenStop(): void
    {
        $stream = $this->createMock(TextStreamInterface::class);
        $stream->method('read')
            ->willReturn(
                '1',
                '2',
                '3',
                '4',
                null,
                null,
                null,
                '5',
            );
        
        $timer = $this->createMock(TimeProviderInterface::class);
        $timer->expects($this->exactly(3))
            ->method('sleepMilliseconds');
        
        $storage = $this->createStub(StreamPositionRepositoryInterface::class);
        
        $reader = new TextStreamReader($stream, $timer, $storage);
        
        $reader->registerStopSignal(function () {
            static $i = 0;
            static $status = [...array_fill(0, 8, false), true];
            
            return $status[$i++];
        });
        
        $consumer = $this->createMock(TextLineConsumerInterface::class);
        $consumer->expects($this->exactly(5))
            ->method('consume')
            ->with($this->callback(function (string $line) {
                static $lines = [
                    '1',
                    '2',
                    '3',
                    '4',
                    '5',
                ];
                static $index = 0;
                
                return $lines[$index++] === $line;
            }));
        
        $reader->registerConsumer($consumer);
        
        $reader->start();
    }
    
    #[TestDox('When stream is processed for the first time, don\'t seek, but store the final position')]
    public function testFirstStreamProcess(): void
    {
        $timer = $this->createMock(TimeProviderInterface::class);
        
        $stream = $this->createMock(TextStreamInterface::class);
        $stream->method('getIdentifier')
            ->willReturn('file.log');
        $stream->expects($this->never())
            ->method('seek');
        $stream->method('getPosition')
            ->willReturn(123);
        $stream->method('read')
            ->willReturn(null);
        
        $storage = $this->createMock(StreamPositionRepositoryInterface::class);
        $storage->expects($this->once())
            ->method('has')
            ->willReturn(false);
        $storage->expects($this->never())
            ->method('get');
        $storage->expects($this->once())
            ->method('set')
            ->with('file.log', '123');
        
        $reader = new TextStreamReader($stream, $timer, $storage);
        
        $reader->registerStopSignal(function () {
            static $i = 0;
            // Stop after one read:
            static $status = [false, true];

            return $status[$i++];
        });
        
        // Start first reader without stored position:
        $reader->start();
    }
    
    #[TestDox('When stream is processed for the second time, seek, and store the final position')]
    public function testSecondStreamProcess(): void
    {
        $timer = $this->createMock(TimeProviderInterface::class);
        
        $stream = $this->createMock(TextStreamInterface::class);
        $stream->method('getIdentifier')
            ->willReturn('file.log');
        $stream->expects($this->once())
            ->method('seek')
            ->with(123);
        $stream->method('getPosition')
            ->willReturn(234);
        $stream->method('read')
            ->willReturn(null);
        
        $storage = $this->createMock(StreamPositionRepositoryInterface::class);
        $storage->expects($this->once())
            ->method('has')
            ->with('file.log')
            ->willReturn(true);
        $storage->expects($this->once())
            ->method('get')
            ->with('file.log')
            ->willReturn('123');
        $storage->expects($this->once())
            ->method('set')
            ->with('file.log', '234');
        
        $reader = new TextStreamReader($stream, $timer, $storage);
        
        $reader->registerStopSignal(function () {
            static $i = 0;
            // Stop after one read:
            static $status = [false, true];

            return $status[$i++];
        });
        
        // Start first reader without stored position:
        $reader->start();
    }
}
