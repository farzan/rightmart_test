<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Port\TextLineConsumerInterface;
use App\Domain\Port\TextStreamInterface;
use App\Domain\Port\TimerInterface;
use App\Domain\TextStreamReader;
use App\Tests\Support\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

class TextStreamReaderTest extends UnitTestCase
{
    #[TestDox('Start reading with 4 lines, and then stop after 5 reads')]
    public function startThenRead4LinesThenStop(): void
    {
        $stream = $this->createMock(TextStreamInterface::class);
        $stream->method('read')
            ->willReturn(
                '1',
                '2',
                '3',
                '4',
            );
        $timer = $this->createMock(TimerInterface::class);
        $timer->expects($this->once())
            ->method('sleepMilliseconds');
        
        $reader = new TextStreamReader($stream, $timer);
        
        $reader->registerStopSignal(function () {
            static $i = 0;
            static $status = [
                false,
                false,
                false,
                false,
                false,
                true,
            ];
            
            return $status[$i++];
        });
        
        $consumer = $this->createMock(TextLineConsumerInterface::class);
        $consumer->expects($this->exactly(4))
            ->method('consume')
            ->with($this->callback(function (string $line) {
                static $lines = [
                    '1',
                    '2',
                    '3',
                    '4',
                ];
                static $index = 0;
                
                return $lines[$index++] === $line;
            }));
        
        $reader->registerConsumer($consumer);
        
        $reader->start();
    }
}
