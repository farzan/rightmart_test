<?php

declare(strict_types=1);

namespace App\Application\Ports\Input;

/**
 * Interface for a Text based data stream. The stream should be identifiable,
 * provide current position, and means to seek to a position, so the stream
 * reader be able to resume reading from where it left.
 */
interface TextStreamInterface
{
    public function getIdentifier(): string;
    
    public function read(): string|null;
    
    public function seek(int $position): void;
    
    public function getPosition(): int;
}
