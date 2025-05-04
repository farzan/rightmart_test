<?php

declare(strict_types=1);

namespace AppTests\Unit\Application\Services;

use App\Application\Ports\Input\TextStreamInterface;
use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use App\Application\Ports\Output\LogLineRepositoryInterface;
use App\Application\Ports\Output\TimeProviderInterface;
use App\Application\Services\TextStreamReader;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TextStreamReaderTest extends TestCase
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
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        
        $logLineRepo = $this->createMock(LogLineRepositoryInterface::class);
        $logLineRepo->expects($this->exactly(5))
            ->method('consume')
            ->with($this->callback(function (string $line) {
                dump('xxxx');
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
        
        $reader = $this->getMockBuilder(TextStreamReader::class)
            ->onlyMethods(['shouldContinue'])
            ->setConstructorArgs([$stream, $timer, $storage, $eventDispatcher, $logLineRepo])
            ->getMock();
        $reader->method('shouldContinue')
            ->willReturnCallback(function () {
                static $i = 0;
                static $status = [...array_fill(0, 8, true), false];
                
                return $status[$i++];
            });
        
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
        
        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $logLineRepo = $this->createMock(LogLineRepositoryInterface::class);
        
        $reader = $this->getMockBuilder(TextStreamReader::class)
            ->onlyMethods(['shouldContinue'])
            ->setConstructorArgs([$stream, $timer, $storage, $dispatcher, $logLineRepo])
            ->getMock();
        
        $reader->method('shouldContinue')
            ->willReturnCallback(function () {
                static $i = 0;
                // Stop after one read:
                static $status = [true, false];
                
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
        
        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $logLineRepo = $this->createMock(LogLineRepositoryInterface::class);
        
        $reader = $this->getMockBuilder(TextStreamReader::class)
            ->onlyMethods(['shouldContinue'])
            ->setConstructorArgs([$stream, $timer, $storage, $dispatcher, $logLineRepo])
            ->getMock();
        
        $reader->method('shouldContinue')
            ->willReturnCallback(function () {
                static $i = 0;
                // Stop after one read:
                static $status = [true, false];
                
                return $status[$i++];
            });
        
        // Start first reader without stored position:
        $reader->start();
    }
}
