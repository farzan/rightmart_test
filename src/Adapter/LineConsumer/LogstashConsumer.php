<?php

declare(strict_types=1);

namespace App\Adapter\LineConsumer;

use App\Domain\LogParser\CustomFormatParser;
use App\Domain\Port\TextLineConsumerInterface;

class LogstashConsumer implements TextLineConsumerInterface
{
    private mixed $socket;
    
    public function __construct(
        private readonly CustomFormatParser $parser,
        private readonly ElasticSearchJsonLogPresenter $presenter,
    ) {
        $this->connect();
    }
    
    public function __destruct()
    {
        $this->disconnect();
    }
    
    public function consume(string $line): void
    {
        $parsed = $this->parser->parse($line);
        if ($parsed === null) {
            // @todo We can log malformed log line.
            return;
        }
        $json =  $this->presenter->present($parsed);
        // Logstash needs all jsons be appended with a new-line character:
        $json .= "\n";
        
        fwrite($this->socket, $json);
    }
    
    private function connect(): void
    {
        $this->socket = @fsockopen('logstash', 5000, $errno, $errstr, 1.0);
        if (!$this->socket) {
            throw new \RuntimeException("Failed to connect to Logstash: $errstr ($errno)");
        }
    }
    
    private function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }
}
