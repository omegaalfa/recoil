<?php

declare(strict_types=1);

namespace Recoil\Exception;

use Exception;

/**
 * Exception thrown when an operation times out.
 */
class TimeoutException extends Exception
{
    private float $timeout;

    /**
     * Create a timeout exception.
     *
     * @param float $timeout
     * @return static
     */
    public static function create(float $timeout): static
    {
        $e = new static('Operation timed out after ' . $timeout . ' seconds.');
        $e->timeout = $timeout;
        return $e;
    }

    /**
     * Get the timeout value.
     */
    public function timeout(): float
    {
        return $this->timeout;
    }
}