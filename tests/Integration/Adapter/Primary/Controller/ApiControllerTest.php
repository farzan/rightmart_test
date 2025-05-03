<?php

declare(strict_types=1);

namespace AppTests\Integration\Adapter\Primary\Controller;

use App\Application\Ports\Output\Repository\LogRepositoryInterface;
use App\Domain\LogCounter\LogCountQuery;
use DateTime;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    #[TestDox('Count controller sending proper values to ElasticSearch repository')]
    public function testApiController(): void
    {
        $client = static::createClient(['environment' => 'test']);
        
        $logRepository = $this->createMock(LogRepositoryInterface::class);
        $logRepository->expects($this->once())
            ->method('query')
            ->with(new LogCountQuery(['my-service'], 123, new DateTime('2025-06-01 10:00:00'), null));
        
        $container = static::getContainer();
        $container->set(LogRepositoryInterface::class, $logRepository);
        
        $client->request('GET', '/count', [
            'serviceNames' => ['my-service'],
            'statusCode' => '123',
            'startDate' => '2025-06-01T10:00:00',
        ]);
    }
}
