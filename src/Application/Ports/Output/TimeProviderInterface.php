<?php

declare(strict_types=1);

namespace Application\Ports\Output;

/**
 * This interface provide access to some system time related functionalities.
 */
interface TimeProviderInterface
{
    public function sleepMilliseconds(int $milliseconds): void;
}
