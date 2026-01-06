<?php

declare(strict_types=1); // @codeCoverageIgnore

namespace Recoil\Kernel;

/**
 * Enumeration of kernel states, used by KernelTrait.
 */
final class KernelState
{
    const int STOPPED = 0;
    const int RUNNING = 1;
    const int STOPPING = 2;

    private function __construct()
    {
    }
}
