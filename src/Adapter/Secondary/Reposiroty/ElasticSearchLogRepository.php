<?php

declare(strict_types=1);

namespace Adapter\Secondary\Reposiroty;

use Application\Ports\Output\Repository\DataQueryException;
use Application\Ports\Output\Repository\LogRepositoryInterface;
use DateTimeInterface;
use Domain\LogCounter\LogCount;
use Domain\LogCounter\LogCountQuery;
use Elastic\Elasticsearch\ClientBuilder;
use Exception;

class ElasticSearchLogRepository implements LogRepositoryInterface
{
    public function prepare(): void
    {
        $client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();
        
        // Delete existing index:
        $existsResponse = $client->indices()->exists(['index' => 'aggregated-logs']);
        //        dd($existsResponse);
        if ($existsResponse->getStatusCode() === 200) {
            $client->indices()->delete(['index' => 'aggregated-logs']);
        }
        
        // Delete old mapping:
        $existsResponse = $client->indices()->existsIndexTemplate(['name' => 'aggregated-logs-template']);
        if ($existsResponse->getStatusCode() !== 404) {
            $client->indices()->deleteIndexTemplate(['name' => 'aggregated-logs-template']);
        }
        
        // Create new mapping:
        $client->indices()->putIndexTemplate([
            'name' => 'aggregated-logs-template',
            'body' => [
                'index_patterns' => ['aggregated-logs*'],
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
    }
    
    public function query(LogCountQuery $query): LogCount
    {
        try {
            $client = ClientBuilder::create()
                ->setHosts(['elasticsearch:9200'])
                ->build();
            
            $queryArray = $this->getQuery($query);
            
            if ($queryArray === []) {
                $result = $client->count(['aggregated-logs']);
            } else {
                $result = $client->count([
                    'aggregated-logs',
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
