<?php

declare(strict_types=1);

namespace App\Adapter\Secondary\Repository;

use App\Application\Ports\Output\Repository\DataQueryException;
use App\Application\Ports\Output\Repository\LogRepositoryInterface;
use DateTimeInterface;
use App\Domain\LogCounter\LogCount;
use App\Domain\LogCounter\LogCountQuery;
use Elastic\Elasticsearch\ClientBuilder;
use Exception;

class ElasticSearchLogRepository implements LogRepositoryInterface
{
    public function __construct(
        private readonly string $host,
        private readonly string $indexName,
    ) {
    }
    
    public function prepare(): void
    {
        $client = ClientBuilder::create()
            ->setHosts([$this->host])
            ->build();
        
        // Delete existing index:
        $existsResponse = $client->indices()->exists(['index' => $this->indexName]);
        if ($existsResponse->getStatusCode() === 200) {
            $client->indices()->delete(['index' => $this->indexName]);
        }
        
        // Delete old mapping:
        $existsResponse = $client->indices()->existsIndexTemplate(['name' => $this->indexName . '-template']);
        if ($existsResponse->getStatusCode() !== 404) {
            $client->indices()->deleteIndexTemplate(['name' => $this->indexName . '-template']);
        }
        
        // Create new mapping:
        $client->indices()->putIndexTemplate([
            'name' => $this->indexName . '-template',
            'body' => [
                'index_patterns' => [$this->indexName . '*'],
                'template' => [
                    'mappings' => [
                        'properties' => [
                            '@timestamp' => ['type' => 'date'],
                            '@version' => ['type' => 'keyword'],
                            'service' => ['type' => 'keyword'],
                            'statusCode' => ['type' => 'integer'],
                            'method' => ['type' => 'keyword'],
                            'url' => ['type' => 'keyword'],
                            'datetime' => ['type' => 'date'],
                            'protocol' => ['type' => 'keyword'],
                        ],
                    ],
                ],
                'priority' => 1,
            ],
        ]);
        
        // Create index:
        $client->indices()->create(['index' => $this->indexName]);
    }
    
    public function query(LogCountQuery $query): LogCount
    {
        try {
            $client = ClientBuilder::create()
                ->setHosts([$this->host])
                ->build();
            
            $queryArray = $this->getQuery($query);
            
            if ($queryArray === []) {
                $result = $client->count([$this->indexName]);
            } else {
                $result = $client->count([
                    $this->indexName,
                    'body' => $queryArray,
                ]);
            }
            
            $count = $result['count'];
            
            return new LogCount(counter: $count);
        } catch (Exception $e) {
            
            
            throw new DataQueryException('Could not query data.', previous: $e);
        }
    }
    
    private function getQuery(LogCountQuery $query): array
    {
        $musts = [];
        if (!empty($query->serviceNames)) {
            $musts[] = [
                'terms' => [
                    'service' => $query->serviceNames,
                ]
            ];
        }
        if (!empty($query->statusCode)) {
            $musts[] = [
                'term' => [
                    'statusCode' => $query->statusCode,
                ]
            ];
        }
        if (!empty($query->startDate)) {
            $musts[] = [
                'range' => [
                    'datetime' => [
                        'gte' => $query->startDate->format(DateTimeInterface::ATOM),
                    ],
                ]
            ];
        }
        if (!empty($query->endDate)) {
            $musts[] = [
                'range' => [
                    'datetime' => [
                        'lte' => $query->endDate->format(DateTimeInterface::ATOM),
                    ],
                ]
            ];
        }
        
        $queryArray = [
            'query' => [
                'bool' => [
                    'must' => $musts,
                ]
            ]
        ];
        
        return $queryArray;
    }
}
