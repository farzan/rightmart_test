<?php

declare(strict_types=1);

namespace App\Adapter\Database;

use App\Domain\Port\Database\DatabasePreparerInterface;
use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use stdClass;

class ElasticSearchDatabasePreparer implements DatabasePreparerInterface
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
//                'data_stream' => new stdClass(),
            ],
        ]);
//
    }
}
