<?php

declare(strict_types=1);

namespace App\Adapter\Secondary;

use App\Application\Ports\Output\TextLineConsumerInterface;
use App\Application\Services\LogEntryException;
use App\Application\Services\LogEntryParser;
use Psr\Log\LoggerInterface;
use RuntimeException;

class LogstashConsumer implements TextLineConsumerInterface
{
    private mixed $socket;
    
    public function __construct(
        private readonly LogEntryParser $parser,
        private readonly ElasticSearchJsonLogFormatter $presenter,
        private readonly LoggerInterface $logger,
        private readonly string $host,
        private readonly int $port,
    ) {
        $this->connect();
    }
    
    public function __destruct()
    {
        $this->disconnect();
    }
    
    public function consume(string $line): void
    {
        try {
            $parsed = $this->parser->parse($line);
        } catch (LogEntryException $e) {
            // In case of invalid log format, ignore it.
            $this->logger->error('Could not part log entry', [
                'log' => $line,
                'exception' => $e,
            ]);
            
            return;
        }
        
        $json =  $this->presenter->present($parsed);
        // Logstash needs all jsons be appended with a new-line character:
        $json .= "\n";
        
        fwrite($this->socket, $json);
    }
    
    private function connect(): void
    {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, 1.0);
        if (!$this->socket) {
            throw new RuntimeException("Failed to connect to Logstash: $errstr ($errno)");
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
