<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Port\TextLineConsumerInterface;
use App\Domain\Port\TextStreamInterface;
use App\Domain\Port\TimerInterface;

class TextStreamReader
{
    private TextLineConsumerInterface $consumer;
    
    /**
     * @var callable|null
     */
    private mixed $stopSignalCallback = null;
    
    public function __construct(
        private readonly TextStreamInterface $textStream,
        private readonly TimerInterface $timer,
    ) {
    }
    
    public function start(): void
    {
        while ($this->stopSignalCallback === null || ($this->stopSignalCallback)() === false) {
            $line = $this->textStream->read();
            if ($line !== null) {
                $this->consumer->consume($line);
            } else {
                // If no new line is available, wait 100 ms to prevent busy waiting.
                $this->timer->sleepMilliseconds(100_000);
            }
        }
    }
    
    public function registerConsumer(TextLineConsumerInterface $consumer): void
    {
        $this->consumer = $consumer;
    }
    
    public function registerStopSignal(callable $stopSignalCallback): void
    {
        $this->stopSignalCallback = $stopSignalCallback;
    }
}
