<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Adapter\Secondary\TextFileStream;
use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use App\Application\Ports\Output\TextLineConsumerInterface;
use App\Application\Ports\Output\TimeProviderInterface;
use DomainException;

class TextStreamReaderBuilder
{
    private string $filename;
    
    private bool $shouldTail = true;
    
    private TextLineConsumerInterface $consumer;
    private mixed $stopSignalerCallback;
    
    public function __construct(
        private readonly TimeProviderInterface $timer,
        private readonly StreamPositionRepositoryInterface $storage,
    ) {
    }
    
    public static function create(
        TimeProviderInterface $timer,
        StreamPositionRepositoryInterface $storage,
    ): self {
        return new self($timer, $storage);
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
            throw new DomainException('Filename cannot be empty.', code: 1);
        }
        
        if (empty($this->consumer)) {
            throw new DomainException('Consumer cannot be empty.', code: 2);
        }
    }
}
