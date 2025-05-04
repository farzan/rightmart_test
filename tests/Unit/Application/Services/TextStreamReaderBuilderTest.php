<?php

declare(strict_types=1);

namespace AppTests\Unit\Application\Services;

use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use App\Application\Ports\Output\LogLineRepositoryInterface;
use App\Application\Ports\Output\TimeProviderInterface;
use App\Application\Services\TextStreamReaderBuilder;
use DomainException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TextStreamReaderBuilderTest extends TestCase
{
    #[TestDox('Expect exception when filename is not set')]
    public function testFailOnNoFilename(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionCode(1);
        
        $builder = new TextStreamReaderBuilder(
            $this->createStub(TimeProviderInterface::class),
            $this->createStub(StreamPositionRepositoryInterface::class),
            $this->createStub(EventDispatcherInterface::class),
        );
        
        $builder
            ->setConsumer($this->createStub(LogLineRepositoryInterface::class))
            ->build();
    }
}
