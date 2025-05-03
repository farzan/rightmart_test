<?php
declare(strict_types=1);

namespace App\Adapter\Secondary;

use App\Application\Ports\Input\TextStreamInterface;
use InvalidArgumentException;

readonly class TextFileStream implements TextStreamInterface
{
    /**
     * @var resource
     */
    private mixed $stream;
    
    public function __construct(
        private string $filename,
    ) {
        if (!file_exists($filename)) {
            throw new InvalidArgumentException(sprintf("File does not exist: %s", $filename));
        }
        
        $this->stream = fopen($filename, 'r');
    }
    
    public function __destruct()
    {
        fclose($this->stream);
    }
    
    public function getIdentifier(): string
    {
        return $this->filename;
    }
    
    public function read(): string|null
    {
        $content = fgets($this->stream);
        
        if ($content === false) {
            // Helps with some file system buffering edge cases:
            clearstatcache();
            // Re-seek to current position to refresh EOF state:
            fseek($this->stream, 0, SEEK_CUR);
            
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