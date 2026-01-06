<?php

declare(strict_types=1);

namespace Recoil\Exception;

use Exception;
use Recoil\Strand\Strand;
use Throwable;

/**
 * Exception thrown when a strand listener throws an exception.
 */
class StrandListenerException extends Exception
{
    private Strand $strand;

    /**
     * @param Strand $strand
     * @param Throwable $previous
     */
    public function __construct(Strand $strand, Throwable $previous)
    {
        parent::__construct(
            'Listener for strand #' . $strand->id() . ' threw an exception: ' . $previous->getMessage(),
            0,
            $previous
        );
        $this->strand = $strand;
    }

    /**
     * Get the strand that caused this exception.
     */
    public function strand(): Strand
    {
        return $this->strand;
    }
}
