<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Input\TextStreamInterface;
use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use App\Application\Ports\Output\LogLineRepositoryInterface;
use App\Application\Ports\Output\TimeProviderInterface;

class TextStreamReader
{
    const int DELAY_IN_MILLISECONDS = 100;
    
    private LogLineRepositoryInterface $consumer;
    
    /**
     * @var callable|null
     */
    private mixed $stopSignalCallback = null;
    
    public function __construct(
        private readonly TextStreamInterface $textStream,
        private readonly TimeProviderInterface $timer,
        private readonly StreamPositionRepositoryInterface $storage,
        private readonly bool $shouldTail = true,
    ) {
    }
    
    public function start(): void
    {
        $this->seekStream();
        $this->ingestLogs();
        $this->storeStreamPosition();
    }
    
    public function registerConsumer(LogLineRepositoryInterface $consumer): void
    {
        $this->consumer = $consumer;
    }
    
    public function registerStopSignal(callable $stopSignalCallback): void
    {
        $this->stopSignalCallback = $stopSignalCallback;
    }
    
    private function ingestLogs(): void
    {
        while ($this->shouldContinue()) {
            $line = $this->textStream->read();
            
            if ($line !== null) {
                $this->consumer->consume($line);
            } else {
                // If not in Tail mode, end reading.
                if (!$this->shouldTail) {
                    return;
                }
                
                // If no new line is available, wait 100 ms to prevent busy waiting.
                $this->timer->sleepMilliseconds(self::DELAY_IN_MILLISECONDS);
            }
        }
    }
    
    private function seekStream(): void
    {
        $identifier = $this->textStream->getIdentifier();
        if ($this->storage->has($identifier)) {
            $this->textStream->seek((int) $this->storage->get($identifier));
        }
    }
    
    private function storeStreamPosition(): void
    {
        $this->storage->set(
            key: $this->textStream->getIdentifier(),
            value: (string) $this->textStream->getPosition(),
        );
    }
    
    /**
     * @return bool
     */
    private function shouldContinue(): bool
    {
        return $this->stopSignalCallback === null || ($this->stopSignalCallback)() === false;
    }
}
