<?php

declare(strict_types=1);

namespace AppTests\Integration\Adapter\Primary\Command;

use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use App\Application\Ports\Output\LogLineRepositoryInterface;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class IngestLogsCommandTest extends KernelTestCase
{
    #[TestDox('Run command with test file')]
    public function testRunCommand(): void
    {
        self::bootKernel(['environment' => 'test']);
        
        $streamPositionRepository = $this->createStub(StreamPositionRepositoryInterface::class);
        $streamPositionRepository->method('has')
            ->willReturn(false);
        $lineConsumer = $this->createMock(LogLineRepositoryInterface::class);
        $lineConsumer->expects($this->exactly(20))
            ->method('consume');
        
        $container = static::getContainer();
        $container->set(StreamPositionRepositoryInterface::class, $streamPositionRepository);
        $container->set(LogLineRepositoryInterface::class, $lineConsumer);
        
        $app = new Application(self::$kernel);
        
        $command = $app->find('app:ingest-logs');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'logfile' => __DIR__ . '/logs.log',
            '--no-tail' => true,
        ]);
    }
}
