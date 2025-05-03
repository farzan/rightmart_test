<?php

declare(strict_types=1);

namespace AppTests\Unit\Application\Services;

use App\Application\Ports\Output\Repository\LogRepositoryInterface;
use App\Application\Services\LogService;
use AppTests\Support\UnitTestCase;
use App\Domain\LogCounter\LogCount;
use App\Domain\LogCounter\LogCountQuery;

class LogServiceTest extends UnitTestCase
{
    public function testQuery(): void
    {
        $expectedCount = new LogCount(123);
        
        $logRepository = $this->createMock(LogRepositoryInterface::class);
        $logRepository->expects($this->once())
            ->method('query')
            ->willReturn($expectedCount);
        
        $logService = new LogService($logRepository);
        
        $count = $logService->getCount(new LogCountQuery([], null, null, null));
        
        $this->assertSame($expectedCount->counter, $count->counter);
    }
}
