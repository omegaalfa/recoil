<?php

declare(strict_types=1);

namespace Recoil\Exception;

use Exception;
use Recoil\Strand\Strand;
use Throwable;

/**
 * Exception thrown when a strand exits with an unhandled exception.
 */
class StrandException extends Exception
{
    private Strand $strand;

    /**
     * Create a strand exception.
     *
     * @param Strand $strand
     * @param Throwable $exception
     * @return static
     */
    public static function create(Strand $strand, Throwable $exception): static
    {
        $e = new static(
            'Strand #' . $strand->id() . ' exited with exception: ' . $exception->getMessage(),
            $exception->getCode(),
            $exception
        );
        $e->strand = $strand;
        return $e;
    }

    /**
     * Get the strand that caused this exception.
     */
    public function strand(): Strand
    {
        return $this->strand;
    }
}