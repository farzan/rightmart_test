<?php

declare(strict_types=1);

namespace App\Adapter\Database;

use App\Domain\Port\Database\DatabaseException;
use App\Domain\Port\DataService\CountQuery;
use App\Domain\Port\DataService\CountQueryResult;
use App\Domain\Port\Database\LogCountQueryRunnerInterface;
use DateTimeInterface;
use Elastic\Elasticsearch\ClientBuilder;
use Exception;

class ElasticSearchLogCountQueryRunner implements LogCountQueryRunnerInterface
{
    /**
     * @throws DatabaseException
     */
    public function query(CountQuery $query): CountQueryResult
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
            
//            dd($result);
            
            $count = $result['count'];
            
//            dd($result->asArray());
            
            return new CountQueryResult(counter: $count);
        } catch (Exception $e) {
            throw new DatabaseException('Could not fulfill database operation.', previous: $e);
        }
    }
    
    private function getQuery(CountQuery $query): array
    {
//        dump($query);
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
        
//        dump($queryArray);
        return $queryArray;
    }
}
