<?php

declare(strict_types=1);

namespace AppTests\Unit\Application\Services;

use App\Application\Ports\Output\Repository\StreamPositionRepositoryInterface;
use App\Application\Ports\Output\TextLineConsumerInterface;
use App\Application\Ports\Output\TimeProviderInterface;
use App\Application\Services\TextStreamReaderBuilder;
use DomainException;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

class TextStreamReaderBuilderTest extends TestCase
{
    #[TestDox('Expect exception when filename is not set')]
    public function testFailOnNoFilename(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionCode(1);
        
        TextStreamReaderBuilder::create(
            $this->createStub(TimeProviderInterface::class),
            $this->createStub(StreamPositionRepositoryInterface::class),
        )
            ->setConsumer($this->createStub(TextLineConsumerInterface::class))
            ->build();
    }
    
    #[TestDox('Expect exception when consumer is not set')]
    public function testFailOnNoConsumer(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionCode(2);
        
        TextStreamReaderBuilder::create(
            $this->createStub(TimeProviderInterface::class),
            $this->createStub(StreamPositionRepositoryInterface::class),
        )
            ->setFilename('filename')
            ->build();
    }
}
