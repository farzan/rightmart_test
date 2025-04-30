<?php
declare(strict_types=1);

namespace App\Adapter\Io;

use App\Domain\Port\TextStreamInterface;
use InvalidArgumentException;
//use resource;

class TextFileStream implements TextStreamInterface
{
    private readonly mixed $stream;
    
    public function __construct(
        string $filename,
    ) {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(sprintf("File does not exist: %s", $filename));
        }
        
        $this->stream = fopen($filename, 'r');
    }
    
    public function read(): string|null
    {
        $content = fgets($this->stream);
        
        if ($content === false) {
            return null;
        }
        
        return rtrim($content);
    }
    
    public function seek(int $position): void
    {
        fseek($this->stream, $position);
    }
    
    public function getPosition(): int
    {
        return ftell($this->stream);
    }
}