<?php

declare(strict_types=1);

namespace App\Adapter\Secondary\Repository;

use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class JsonStreamPositionRepository implements StreamPositionRepositoryInterface
{
    private string $jsonFile;
    
    private array $keyValues;
    
    public function __construct(
        ParameterBagInterface $parameterBag,
        private readonly string $storageFile,
    ) {
        $this->jsonFile = $parameterBag->get('kernel.project_dir') . '/' . $this->storageFile;
        
        if (!file_exists($this->jsonFile)) {
            file_put_contents($this->jsonFile, json_encode(new stdClass()));
        }
        
        $this->keyValues = json_decode(file_get_contents($this->jsonFile), true);
    }
    
    public function has(string $key): bool
    {
        return isset($this->keyValues[$key]);
    }
    
    public function get(string $key): string
    {
        return $this->keyValues[$key];
    }
    
    public function set(string $key, string $value): void
    {
        $this->keyValues[$key] = $value;
        
        $this->writeData();
    }
    
    private function writeData(): void
    {
        file_put_contents($this->jsonFile, json_encode($this->keyValues));
    }
}
