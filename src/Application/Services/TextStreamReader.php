<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\Input\TextStreamInterface;
use App\Application\Ports\Output\LogLineRepositoryInterface;
use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use App\Application\Ports\Output\TimeProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TextStreamReader
{
    const int DELAY_IN_MILLISECONDS = 100;
    
    private bool $terminate = false;
    
    public function __construct(
        private readonly TextStreamInterface $textStream,
        private readonly TimeProviderInterface $timer,
        private readonly StreamPositionRepositoryInterface $storage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LogLineRepositoryInterface $consumer,
        private readonly bool $shouldTail = true,
    ) {
        $this->eventDispatcher->addListener(
            TerminateCommandEvent::EVENT_NAME,
            function (TerminateCommandEvent $event) {
                $this->terminate = true;
            },
        );
    }
    
    public function start(): void
    {
        $this->seekStream();
        $this->ingestLogs();
        $this->storeStreamPosition();
    }
    
    public function shouldContinue(): bool
    {
        return !$this->terminate;
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
}
