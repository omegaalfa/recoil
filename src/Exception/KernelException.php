<?php

declare(strict_types=1);

namespace Recoil\Exception;

use Exception;
use Throwable;

/**
 * Exception thrown when a kernel error occurs.
 */
class KernelException extends Exception
{
    /**
     * Create a kernel exception from a throwable.
     *
     * @param Throwable $e
     * @return static
     */
    public static function create(Throwable $e): static
    {
        return new static(
            'Kernel panic: ' . $e->getMessage(),
            $e->getCode(),
            $e
        );
    }
}