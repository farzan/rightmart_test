<?php

declare(strict_types=1);

namespace App\Domain\Port;

interface TextStreamInterface
{
    public function read(): string|null;
    
    public function seek(int $position): void;
    
    public function getPosition(): int;
}
