<?php

declare(strict_types=1); // @codeCoverageIgnore

namespace Recoil\System;


use Recoil\Kernel\Kernel;
use Recoil\Listener\Listener;

/**
 * A low-level kernel interface for use within the kernel.
 */
interface SystemKernel extends Kernel, Listener
{
}
