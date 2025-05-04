<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Adapter\Secondary\TextFileStream;
use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use App\Application\Ports\Output\LogLineRepositoryInterface;
use App\Application\Ports\Output\TimeProviderInterface;
use DomainException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TextStreamReaderBuilder
{
    private string $filename;
    
    private bool $shouldTail = true;
    
    private LogLineRepositoryInterface $consumer;
    
    public function __construct(
        private readonly TimeProviderInterface $timer,
        private readonly StreamPositionRepositoryInterface $storage,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }
    
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        
        return $this;
    }
    
    public function setConsumer(LogLineRepositoryInterface $consumer): self
    {
        $this->consumer = $consumer;
        
        return $this;
    }
    
    public function shouldTail(bool $shouldTail): self
    {
        $this->shouldTail = $shouldTail;
        
        return $this;
    }
    
    public function build(): TextStreamReader
    {
        $this->validate();
        
        $reader = new TextStreamReader(
            new TextFileStream($this->filename),
            $this->timer,
            $this->storage,
            $this->eventDispatcher,
            $this->consumer,
            $this->shouldTail,
        );
        
        return $reader;
    }
    
    private function validate(): void
    {
        if (empty($this->filename)) {
            throw new DomainException('Filename cannot be empty.', code: 1);
        }
        
        if (empty($this->consumer)) {
            throw new DomainException('Consumer cannot be empty.', code: 2);
        }
    }
}
