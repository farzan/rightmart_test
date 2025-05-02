<?php

declare(strict_types=1);

namespace AppTests\Integration;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class IngestLogsCommandTest extends KernelTestCase
{
    #[TestDox('Command execution with sample logs')]
    public function testCommand(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);
        
        $command = $application->find('app:ingest-logs');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'logfile' => __DIR__ . '/logs.log',
        ]);
        
        $commandTester->assertCommandIsSuccessful();
    }
}
