<?php

declare(strict_types=1);

namespace App\Domain;

use App\Adapter\Io\TextFileStream;
use App\Domain\Port\KeyValueStorageInterface;
use App\Domain\Port\TextLineConsumerInterface;
use App\Domain\Port\TimerInterface;
use DomainException;

class TextStreamReaderBuilder
{
    private string $filename;
    
    private bool $shouldTail = true;
    
    private TextLineConsumerInterface $consumer;
    private mixed $stopSignalerCallback;
    
    public function __construct(
        private readonly TimerInterface $timer,
        private readonly KeyValueStorageInterface $storage,
    ) {
    }
    
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        
        return $this;
    }
    
    public function setConsumer(TextLineConsumerInterface $consumer): self
    {
        $this->consumer = $consumer;
        
        return $this;
    }
    
    public function shouldTail(bool $shouldTail): self
    {
        $this->shouldTail = $shouldTail;
        
        return $this;
    }
    
    public function setStopSignaler(callable $stopSignalerCallback): self
    {
        $this->stopSignalerCallback = $stopSignalerCallback;
        
        return $this;
    }
    
    public function build(): TextStreamReader
    {
        $this->validate();
        
        $reader = new TextStreamReader(
            new TextFileStream($this->filename),
            $this->timer,
            $this->storage,
            $this->shouldTail,
        );
        $reader->registerConsumer($this->consumer);
        if (!empty($this->stopSignalerCallback)) {
            $reader->registerStopSignal($this->stopSignalerCallback);
        }
        
        return $reader;
    }
    
    private function validate(): void
    {
        if (empty($this->filename)) {
            throw new DomainException('Filename cannot be empty.');
        }
        
        if (empty($this->consumer)) {
            throw new DomainException('Consumer cannot be empty.');
        }
    }
}
